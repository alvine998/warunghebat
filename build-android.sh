#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ANDROID_DIR="$ROOT_DIR/android-twa"
ANDROID_SDK_ROOT="${ANDROID_SDK_ROOT:-${ANDROID_HOME:-$HOME/Library/Android/sdk}}"
JAVA_HOME="${JAVA_HOME:-$(/usr/libexec/java_home -v 17 2>/dev/null || true)}"
NPM_CACHE_DIR="$ROOT_DIR/.cache/npm"
BUBBLEWRAP_VERSION="1.25.0"
BUILD_SDK_ROOT="$ROOT_DIR/.cache/android-sdk"
KEYSTORE_PATH="$ANDROID_DIR/android-keystore.jks"
SIGNING_ENV_PATH="$ANDROID_DIR/.signing.env"
MANIFEST_PATH="$ANDROID_DIR/twa-manifest.json"

if [[ -z "$JAVA_HOME" || ! -x "$JAVA_HOME/bin/java" ]]; then
    printf 'Java 17 is required. Set JAVA_HOME to a JDK 17 installation.\n' >&2
    exit 1
fi

if [[ ! -x "$ANDROID_SDK_ROOT/cmdline-tools/latest/bin/sdkmanager" ]]; then
    printf 'Android SDK command-line tools were not found at %s. Set ANDROID_SDK_ROOT.\n' "$ANDROID_SDK_ROOT" >&2
    exit 1
fi

mkdir -p "$ANDROID_DIR" "$NPM_CACHE_DIR" "$BUILD_SDK_ROOT"
for sdk_component in build-tools platforms platform-tools licenses cmdline-tools; do
    if [[ -e "$BUILD_SDK_ROOT/$sdk_component" && ! -L "$BUILD_SDK_ROOT/$sdk_component" ]]; then
        printf 'Unexpected existing Android SDK compatibility path: %s\n' "$BUILD_SDK_ROOT/$sdk_component" >&2
        exit 1
    fi
    if [[ ! -e "$BUILD_SDK_ROOT/$sdk_component" ]]; then
        ln -s "$ANDROID_SDK_ROOT/$sdk_component" "$BUILD_SDK_ROOT/$sdk_component"
    fi
done
mkdir -p "$BUILD_SDK_ROOT/bin"
ln -sf "$ANDROID_SDK_ROOT/cmdline-tools/latest/bin/sdkmanager" "$BUILD_SDK_ROOT/bin/sdkmanager"
export JAVA_HOME ANDROID_HOME="$BUILD_SDK_ROOT" ANDROID_SDK_ROOT="$BUILD_SDK_ROOT"
export PATH="$JAVA_HOME/bin:$BUILD_SDK_ROOT/platform-tools:$PATH"

JDK_ROOT="$(cd "$JAVA_HOME/../.." && pwd)"
node - "$HOME/.bubblewrap/config.json" "$JDK_ROOT" "$BUILD_SDK_ROOT" <<'NODE'
const fs = require('node:fs');
const path = require('node:path');
const [configPath, jdkPath, androidSdkPath] = process.argv.slice(2);
let config = {};
if (fs.existsSync(configPath)) {
    config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
}
if (!config.jdkPath || !fs.existsSync(path.join(config.jdkPath, 'Contents', 'Home', 'bin', 'java'))) {
    config.jdkPath = jdkPath;
}
if (!config.androidSdkPath || (!fs.existsSync(path.join(config.androidSdkPath, 'tools')) && !fs.existsSync(path.join(config.androidSdkPath, 'bin')))) {
    config.androidSdkPath = androidSdkPath;
}
fs.mkdirSync(path.dirname(configPath), { recursive: true });
fs.writeFileSync(configPath, `${JSON.stringify(config, null, 2)}\n`);
NODE

if [[ ! -f "$SIGNING_ENV_PATH" ]]; then
    printf 'Creating a private Android signing key. Keep android-twa/android-keystore.jks and android-twa/.signing.env safe; future Play Store updates must use the same key.\n'
    KEYSTORE_PASSWORD="$(openssl rand -hex 24)"
    KEY_PASSWORD="$KEYSTORE_PASSWORD"
    printf 'KEYSTORE_PASSWORD=%s\nKEY_PASSWORD=%s\n' "$KEYSTORE_PASSWORD" "$KEY_PASSWORD" > "$SIGNING_ENV_PATH"
    chmod 600 "$SIGNING_ENV_PATH"
fi

source "$SIGNING_ENV_PATH"

if [[ -f "$ANDROID_DIR/app-release-bundle.aab" ]]; then
    node - "$MANIFEST_PATH" <<'NODE'
const fs = require('node:fs');
const manifestPath = process.argv[2];
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
manifest.appVersionCode += 1;
const version = manifest.appVersion.split('.');
if (version.length === 3 && version.every((part) => /^\d+$/.test(part))) {
    version[2] = String(Number(version[2]) + 1);
    manifest.appVersion = version.join('.');
}
fs.writeFileSync(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`);
NODE
fi

if [[ ! -f "$KEYSTORE_PATH" ]]; then
    "$JAVA_HOME/bin/keytool" -genkeypair -v \
        -keystore "$KEYSTORE_PATH" \
        -storetype JKS \
        -alias warunghebat \
        -keyalg RSA \
        -keysize 2048 \
        -validity 10000 \
        -dname 'CN=Warung Hebat, OU=Mobile, O=Warung Hebat, L=Jakarta, ST=DKI Jakarta, C=ID' \
        -storepass "$KEYSTORE_PASSWORD" \
        -keypass "$KEY_PASSWORD"
    chmod 600 "$KEYSTORE_PATH"
fi


export BUBBLEWRAP_KEYSTORE_PASSWORD="$KEYSTORE_PASSWORD"
export BUBBLEWRAP_KEY_PASSWORD="$KEY_PASSWORD"
unset KEYSTORE_PASSWORD KEY_PASSWORD


NPM_CONFIG_CACHE="$NPM_CACHE_DIR" npx --yes --package="@bubblewrap/cli@$BUBBLEWRAP_VERSION" bubblewrap update \
    --manifest="$MANIFEST_PATH" \
    --directory="$ANDROID_DIR" \
    --skipVersionUpgrade

(
    cd "$ANDROID_DIR"
    NPM_CONFIG_CACHE="$NPM_CACHE_DIR" npx --yes --package="@bubblewrap/cli@$BUBBLEWRAP_VERSION" bubblewrap build \
        --manifest="$MANIFEST_PATH" \
        --skipSigning
    "$JAVA_HOME/bin/jarsigner" \
        -keystore "$KEYSTORE_PATH" \
        -storepass "$BUBBLEWRAP_KEYSTORE_PASSWORD" \
        -keypass "$BUBBLEWRAP_KEY_PASSWORD" \
        -signedjar app-release-bundle.aab \
        app/build/outputs/bundle/release/app-release.aab \
        warunghebat
    "$JAVA_HOME/bin/jarsigner" -verify app-release-bundle.aab
)

KEY_FINGERPRINT="$("$JAVA_HOME/bin/keytool" -list -v -keystore "$KEYSTORE_PATH" -alias warunghebat -storepass "$BUBBLEWRAP_KEYSTORE_PASSWORD" | awk -F': ' '/SHA256:/{print $2; exit}' | tr -d ':' | tr '[:upper:]' '[:lower:]')"
ASSETLINKS_PATH="$ROOT_DIR/public/.well-known/assetlinks.json"
mkdir -p "$(dirname "$ASSETLINKS_PATH")"
node - "$MANIFEST_PATH" "$KEY_FINGERPRINT" "$ASSETLINKS_PATH" <<'NODE'
const fs = require('node:fs');
const [manifestPath, fingerprint, assetLinksPath] = process.argv.slice(2);
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const assetLinks = [{
    relation: ['delegate_permission/common.handle_all_urls'],
    target: {
        namespace: 'android_app',
        package_name: manifest.packageId,
        sha256_cert_fingerprints: [fingerprint.match(/.{2}/g).join(':').toUpperCase()],
    },
}];
fs.writeFileSync(assetLinksPath, `${JSON.stringify(assetLinks, null, 2)}\n`);
NODE

printf '\nApp Bundle: %s\n' "$ANDROID_DIR/app-release-bundle.aab"
printf 'Digital Asset Links: %s\n' "$ASSETLINKS_PATH"
printf 'Deploy assetlinks.json to https://warunghebat.com/.well-known/assetlinks.json to enable verified fullscreen mode.\n'
