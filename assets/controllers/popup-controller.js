import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        popupId: Number,
        triggerType: String,
        triggerDelay: { type: Number, default: 5 },
        scrollDepth: { type: Number, default: 50 },
        showFrequency: String,
        subscribeUrl: String,
        csrfToken: String,
        hasCart: { type: Boolean, default: false },
    };

    static targets = ['overlay', 'emailInput', 'emailForm', 'thankYou', 'copyFeedback', 'honeypot'];

    connect() {
        if (this._alreadyShown()) {
            return;
        }

        this._setupTrigger();
    }

    disconnect() {
        this._cleanup();
    }

    show() {
        if (this._shown) return;
        this._shown = true;

        this.overlayTarget.classList.add('sylius-popup--visible');
        this._markShown();
    }

    hide() {
        this.overlayTarget.classList.remove('sylius-popup--visible');
    }

    copyCode(event) {
        const code = event.currentTarget.dataset.code;
        navigator.clipboard.writeText(code).then(() => {
            if (this.hasCopyFeedbackTarget) {
                this.copyFeedbackTarget.textContent = this.copyFeedbackTarget.dataset.successText || 'Copied!';
                setTimeout(() => {
                    this.copyFeedbackTarget.textContent = '';
                }, 2000);
            }
        });
    }

    submitEmail(event) {
        event.preventDefault();

        if (!this.hasEmailInputTarget) return;

        const email = this.emailInputTarget.value.trim();
        if (!email) return;

        const payload = { email };

        // Include honeypot value (should be empty for real users)
        if (this.hasHoneypotTarget) {
            payload.website = this.honeypotTarget.value;
        }

        fetch(this.subscribeUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': this.csrfTokenValue,
            },
            body: JSON.stringify(payload),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    if (this.hasEmailFormTarget) {
                        this.emailFormTarget.style.display = 'none';
                    }
                    if (this.hasThankYouTarget) {
                        this.thankYouTarget.style.display = 'block';
                    }
                }
            })
            .catch(() => {
                // Silently fail — don't disrupt the user experience
            });
    }

    // Private methods

    _setupTrigger() {
        switch (this.triggerTypeValue) {
            case 'exit_intent':
                this._setupExitIntent();
                break;
            case 'time_on_page':
                this._setupTimeOnPage();
                break;
            case 'scroll_depth':
                this._setupScrollDepth();
                break;
            case 'cart_abandonment':
                this._setupCartAbandonment();
                break;
        }
    }

    _setupExitIntent() {
        this._exitHandler = (e) => {
            if (e.clientY <= 0) {
                this.show();
                document.removeEventListener('mouseleave', this._exitHandler);
            }
        };
        document.addEventListener('mouseleave', this._exitHandler);
    }

    _setupTimeOnPage() {
        this._timer = setTimeout(() => {
            this.show();
        }, this.triggerDelayValue * 1000);
    }

    _setupScrollDepth() {
        this._scrollHandler = () => {
            const scrollPercent =
                (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent >= this.scrollDepthValue) {
                this.show();
                window.removeEventListener('scroll', this._scrollHandler);
            }
        };
        window.addEventListener('scroll', this._scrollHandler, { passive: true });
    }

    _setupCartAbandonment() {
        if (!this.hasCartValue) return;

        this._exitHandler = (e) => {
            if (e.clientY <= 0) {
                this.show();
                document.removeEventListener('mouseleave', this._exitHandler);
            }
        };
        document.addEventListener('mouseleave', this._exitHandler);
    }

    _cleanup() {
        if (this._exitHandler) {
            document.removeEventListener('mouseleave', this._exitHandler);
        }
        if (this._timer) {
            clearTimeout(this._timer);
        }
        if (this._scrollHandler) {
            window.removeEventListener('scroll', this._scrollHandler);
        }
    }

    _storageKey() {
        return `sylius_popup_${this.popupIdValue}`;
    }

    _alreadyShown() {
        const stored = localStorage.getItem(this._storageKey());
        if (!stored) return false;

        const timestamp = parseInt(stored, 10);

        switch (this.showFrequencyValue) {
            case 'session':
                // sessionStorage would be more appropriate, but we use localStorage
                // with a session-scoped flag via sessionStorage
                return sessionStorage.getItem(this._storageKey()) !== null;
            case 'day':
                return Date.now() - timestamp < 24 * 60 * 60 * 1000;
            case 'week':
                return Date.now() - timestamp < 7 * 24 * 60 * 60 * 1000;
            default:
                return false;
        }
    }

    _markShown() {
        const now = Date.now().toString();
        localStorage.setItem(this._storageKey(), now);

        if (this.showFrequencyValue === 'session') {
            sessionStorage.setItem(this._storageKey(), now);
        }
    }
}
