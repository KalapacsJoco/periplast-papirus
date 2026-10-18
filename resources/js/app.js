const tabStorageKey = 'papirus.auth.tab';

let tabId = sessionStorage.getItem(tabStorageKey);

if (!tabId) {
    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
    tabId = `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    sessionStorage.setItem(tabStorageKey, tabId);
}

const currentUrl = new URL(window.location.href);

if (currentUrl.pathname === '/' && currentUrl.searchParams.get('papirus_tab') !== tabId) {
    currentUrl.searchParams.set('papirus_tab', tabId);
    window.location.replace(currentUrl);
} else if (currentUrl.pathname === '/' && currentUrl.searchParams.has('papirus_tab')) {
    currentUrl.searchParams.delete('papirus_tab');
    window.history.replaceState(null, '', currentUrl);
}
