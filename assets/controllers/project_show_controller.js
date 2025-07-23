// assets/controllers/project_show_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['archiveButton', 'deadline', 'deleteOrphans', 'resyncButton', 'gammeButton'];
    static values = {
        projectId: String
    }

    connect() {
        // S'assurer que le projectId est défini
        if (!this.projectIdValue) {
            console.error('Project ID is not defined');
            return;
        }
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

    // Utilitaire pour les notifications
    notify(type, message) {
        const event = new CustomEvent('toast:' + type, {
            detail: { message }
        });
        document.dispatchEvent(event);
    }
}
