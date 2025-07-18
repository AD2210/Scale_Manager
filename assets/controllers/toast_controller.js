import { Controller } from '@hotwired/stimulus';
import { Toast } from 'bootstrap';

export default class extends Controller {
    static targets = ['container'];
    static values = {
        duration: { type: Number, default: 3000 }
    };

    connect() {
        this.toasts = [];

        // Ajout des écouteurs d'événements
        document.addEventListener('toast:success', this.success.bind(this));
        document.addEventListener('toast:error', this.error.bind(this));
        document.addEventListener('toast:warning', this.warning.bind(this));
    }

    disconnect() {
        // Nettoyage des écouteurs d'événements
        document.removeEventListener('toast:success', this.success.bind(this));
        document.removeEventListener('toast:error', this.error.bind(this));
        document.removeEventListener('toast:warning', this.warning.bind(this));
    }

    /**
     * Crée et affiche un toast
     * @param {string} message - Le message à afficher
     * @param {string} type - Le type de toast ('success', 'error', 'warning')
     * @private
     */
    _showToast(message, type) {
        // Créer l'élément toast
        const toastElement = document.createElement('div');
        const isWarning = type === 'warning';

        toastElement.className = `toast align-items-center border-0 ${
            isWarning ? 'text-dark' : 'text-white'
        } ${this._getBackgroundClass(type)}`;

        toastElement.setAttribute('role', 'alert');
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');

        // Créer le contenu du toast
        toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${this._getIcon(type)} ${message}
            </div>
            <button type="button" class="btn-close ${
                isWarning ? '' : 'btn-close-white'
            } me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    `;

        // Ajouter le toast au container
        this.containerTarget.appendChild(toastElement);

        // Créer et configurer l'instance Bootstrap Toast
        const toast = new Toast(toastElement, {
            autohide: true,
            delay: this.durationValue
        });

        // Nettoyer le DOM après la fermeture
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });

        // Afficher le toast
        toast.show();
    }

    /**
     * Retourne la classe de couleur de fond appropriée
     * @param {string} type - Le type de toast
     * @returns {string} - La classe CSS
     * @private
     */
    _getBackgroundClass(type) {
        switch (type) {
            case 'success':
                return 'bg-success';
            case 'error':
                return 'bg-danger';
            case 'warning':
                return 'bg-warning';
            default:
                return 'bg-primary';
        }
    }

    /**
     * Retourne l'icône FontAwesome appropriée
     * @param {string} type - Le type de toast
     * @returns {string} - Le HTML de l'icône
     * @private
     */
    _getIcon(type) {
        switch (type) {
            case 'success':
                return '<i class="fas fa-check-circle me-2"></i>';
            case 'error':
                return '<i class="fas fa-exclamation-circle me-2"></i>';
            case 'warning':
                return '<i class="fas fa-exclamation-triangle me-2"></i>';
            default:
                return '<i class="fas fa-info-circle me-2"></i>';
        }
    }

    // Méthodes publiques pour afficher les différents types de toasts
    success(event) {
        const message = event.detail?.message || 'Opération réussie';
        this._showToast(message, 'success');
    }

    error(event) {
        const message = event.detail?.message || 'Une erreur est survenue';
        this._showToast(message, 'error');
    }

    warning(event) {
        const message = event.detail?.message || 'Attention';
        this._showToast(message, 'warning');
    }
}
