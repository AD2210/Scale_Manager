// assets/controllers/project_index_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['searchInput', 'archiveToggle'];

    connect() {
        // On cache les projets archivés par défaut
        this.toggleArchivedProjects();
    }

    search() {
        const searchTerm = this.searchInputTarget.value.toLowerCase();
        const projectCards = document.querySelectorAll('.project-card');

        projectCards.forEach(card => {
            const title = card.querySelector('.card-title').textContent.toLowerCase();
            const customer = card.querySelector('[data-customer]').dataset.customer.toLowerCase();
            const manager = card.querySelector('[data-manager]').dataset.manager.toLowerCase();

            const matches = title.includes(searchTerm) ||
                          customer.includes(searchTerm) ||
                          manager.includes(searchTerm);

            card.style.display = matches ? '' : 'none';
        });
    }

    toggleArchivedProjects() {
        const showArchived = this.archiveToggleTarget.checked;
        const projectCards = document.querySelectorAll('.project-card');

        projectCards.forEach(card => {
            const isArchived = card.dataset.archived === 'true';
            if (isArchived) {
                card.style.display = showArchived ? '' : 'none';
            }
        });
    }

    async toggleArchiveStatus(event) {
        event.preventDefault();
        const badge = event.currentTarget;
        const projectId = badge.dataset.id;
        const card = badge.closest('.project-card');
        const currentlyArchived = card.dataset.archived === 'true';

        try {
            const response = await fetch(`/project/${projectId}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ archived: !currentlyArchived })
            });

            if (!response.ok) throw new Error('Erreur lors de la mise à jour');

            // Mettre à jour l'état visuel
            card.dataset.archived = (!currentlyArchived).toString();
            badge.classList.remove(currentlyArchived ? 'bg-secondary' : 'bg-success');
            badge.classList.add(currentlyArchived ? 'bg-success' : 'bg-secondary');
            badge.textContent = currentlyArchived ? 'En cours' : 'Archivé';

            // Si on masque les archives et qu'on vient d'archiver, on cache la carte
            if (!currentlyArchived && !this.archiveToggleTarget.checked) {
                card.style.display = 'none';
            }

            // Notification
            const event = new CustomEvent('toast:success', {
                detail: { message: `Projet ${!currentlyArchived ? 'archivé' : 'désarchivé'} avec succès` }
            });
            document.dispatchEvent(event);

        } catch (error) {
            const event = new CustomEvent('toast:error', {
                detail: { message: 'Erreur lors de la mise à jour du statut' }
            });
            document.dispatchEvent(event);
        }
    }
}
