const initializeCookieConsent = () => {
    const banner = document.getElementById('cookie-banner');
    const acceptButton = document.getElementById('cookie-accept');
    const refuseButton = document.getElementById('cookie-refuse');

    if (!banner || !acceptButton || !refuseButton) {
        return;
    }

    const consent = window.localStorage.getItem('cookie_consent');

    if (!consent) {
        banner.hidden = false;
    }

    const hideBanner = (value) => {
        window.localStorage.setItem('cookie_consent', value);
        banner.hidden = true;
    };

    acceptButton.addEventListener('click', () => hideBanner('accepted'));
    refuseButton.addEventListener('click', () => hideBanner('refused'));
};

document.addEventListener('DOMContentLoaded', initializeCookieConsent);
