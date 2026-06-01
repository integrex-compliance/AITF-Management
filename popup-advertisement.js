// Popup Advertisement System
class PopupAdvertisement {
    constructor() {
        this.popupId = 'audit-info-popup';
        this.sessionKey = 'popup_closed_session';
        this.delay = 5000; // 5 seconds
        this.init();
    }

    init() {
        // Check if popup was already closed in this session
        if (sessionStorage.getItem(this.sessionKey)) {
            return;
        }

        // Show popup after delay
        setTimeout(() => {
            this.showPopup();
        }, this.delay);
    }

    createPopup() {
        const popupHTML = `
            <div id="${this.popupId}" class="popup-overlay">
                <div class="popup-container">
                    <button class="popup-close" onclick="popupAd.closePopup()">&times;</button>
                    <div class="popup-content">
                        <div class="popup-icon-header">📋</div>
                        <h3>Apply Online Now!</h3>
                        <p class="popup-message">
                            Submit your <strong>Audit Application</strong> or <strong>Information Update</strong> form quickly and easily.
                        </p>
                        <div class="popup-buttons">
                            <a href="addAuditApplication.php" class="popup-btn audit-btn">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Audit Application</span>
                            </a>
                            <a href="info_updation_form.php" class="popup-btn info-btn">
                                <i class="fas fa-edit"></i>
                                <span>Information Update</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', popupHTML);
    }

    showPopup() {
        this.createPopup();
        const popup = document.getElementById(this.popupId);
        if (popup) {
            popup.style.display = 'block';
            // Add animation
            setTimeout(() => {
                popup.classList.add('popup-show');
            }, 10);
        }
    }

    closePopup() {
        const popup = document.getElementById(this.popupId);
        if (popup) {
            popup.classList.add('popup-hide');
            setTimeout(() => {
                popup.remove();
            }, 300);
            
            // Mark as closed for this session
            sessionStorage.setItem(this.sessionKey, 'true');
        }
    }
}

// Initialize popup when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.popupAd = new PopupAdvertisement();
});