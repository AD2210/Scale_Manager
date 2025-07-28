// assets/controllers/project_show_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['archiveButton', 'deadline', 'deleteOrphans', 'resyncButton', 'gammeButton',
        'uploadInputQuote', 'uploadInputSpecification'];
    static values = {
        projectId: String
    }

    connect() {
        // S'assurer que le projectId est défini
        if (!this.projectIdValue) {
            console.error('Project ID is not defined');
            return;
        }
        // Enregistrer l'état initial
        this.saveState();

        // Ajouter l'écouteur pour popstate
        window.addEventListener('popstate', this.handlePopState.bind(this));
    }

    disconnect() {
        // Nettoyer l'écouteur lors de la déconnexion du contrôleur
        window.removeEventListener('popstate', this.handlePopState.bind(this));
    }

    // Sauvegarde l'état actuel dans l'historique
    saveState() {
        const state = {
            id: this.projectIdValue,
            timestamp: new Date().getTime()
        };
        window.history.replaceState(state, '', window.location.href);
    }

    // Gestion du retour/avant navigateur
    handlePopState(event) {
        // Recharger la page pour assurer la cohérence des données
        window.location.reload();
    }

    // Après chaque action qui modifie l'état
    afterStateChange() {
        this.saveState();
    }

    // Gestion de l'archivage
    async toggleArchive(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const isCurrentlyArchived = button.textContent.trim() === 'Désarchiver';

        try {
            const response = await fetch(`/api/project/${this.projectIdValue}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ archived: !isCurrentlyArchived })
            });

            if (!response.ok) throw new Error('Erreur lors de la mise à jour');

            // Mise à jour du texte du bouton
            button.textContent = isCurrentlyArchived ? 'Archiver' : 'Désarchiver';

            // Notification
            this.notify('success', `Projet ${isCurrentlyArchived ? 'désarchivé' : 'archivé'} avec succès`);
            this.afterStateChange();

        } catch (error) {
            this.notify('error', 'Erreur lors de la mise à jour du statut');
        }
    }

    // Gestion de la deadline
    async updateDeadline(event) {
        const newDate = event.target.value;

        try {
            const response = await fetch(`/api/project/${this.projectIdValue}/deadline`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ deadline: newDate })
            });

            if (!response.ok) throw new Error('Erreur lors de la mise à jour');

            this.notify('success', 'Date limite mise à jour');
            this.afterStateChange();

        } catch (error) {
            this.notify('error', 'Erreur lors de la mise à jour de la date limite');
            // Restaurer l'ancienne valeur en cas d'erreur
            event.target.value = event.target.defaultValue;
        }
    }

    // Gestion de la resynchronisation des dossiers
    async resyncFolders(event) {
        const deleteOrphans = this.deleteOrphansTarget.checked;

        try {
            // Désactiver le bouton pendant la synchronisation
            this.resyncButtonTarget.disabled = true;

            const response = await fetch(`/api/project/${this.projectIdValue}/sync-folders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ deleteOrphans })
            });

            if (!response.ok) throw new Error('Erreur lors de la synchronisation');

            const data = await response.json();

            // Notification avec les statistiques
            this.notify('success', `Synchronisation terminée.
                Ajoutés: ${data.added},
                Supprimés: ${data.deleted},
                Ignorés: ${data.skipped}`);

            // Recharger la page pour mettre à jour les stats
            this.afterStateChange();
            window.location.reload();

        } catch (error) {
            this.notify('error', 'Erreur lors de la synchronisation des dossiers');
        } finally {
            this.resyncButtonTarget.disabled = false;
        }
    }

    // Gestion du bouton Gamme
    async checkModels(event) {
        event.preventDefault();
        const targetUrl = this.gammeButtonTarget.href;

        try {
            const response = await fetch(`/api/project/${this.projectIdValue}/check-models`);
            const data = await response.json();

            if (data.success) {
                window.location.href = targetUrl;
            } else {
                this.notify('warning', data.message);
            }
        } catch (error) {
            this.notify('error', 'Une erreur est survenue lors de la vérification');
        }
    }

    // declencheur de l'input
    triggerFileUpload(event) {
        console.log('init');
        const type = event.currentTarget.dataset.type;
        const input = this[`uploadInput${type}Target`];
        console.log('type : ', type, 'input : ',input);
        if (input) input.click();
    }

    //Gestion upload file
    async handleFileUpload(event) {
        const input = event.target;
        const type = input.dataset.type;
        const projectId = input.dataset.id || this.projectIdValue;

        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(`/project/${projectId}/upload/${type}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            this.notify('success', `Fichier ${type} mis à jour`);
            window.location.reload();

        } catch (e) {
            console.error('Erreur:', e);
            this.notify('error', `Erreur lors du téléversement : ${e.message}`);
        }
    }

    // gestion delete file
    async deleteProjectFile(event) {
        const type = event.currentTarget.dataset.type
        const confirmed = confirm(`Supprimer le fichier ${type} ?`);
        if (!confirmed) return;

        try {
            const response = await fetch(`/project/${this.projectIdValue}/delete-file/${type}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            this.notify('success', `Fichier ${type} supprimé`);
            window.location.reload();

        } catch (e) {
            console.error('Erreur:', e);
            this.notify('error', `Erreur lors de la suppression : ${e.message}`);
        }
    }

    // Utilitaire pour les notifications
    notify(type, message) {
        const event = new CustomEvent('toast:' + type, {
            detail: { message }
        });
        document.dispatchEvent(event);
    }
}
