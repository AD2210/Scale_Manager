import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["select"];

    update(event) {
        const select = event.currentTarget;
        const slot = select.dataset.slot;
        const projectId = select.value;

        if (!projectId) return;

        fetch(`/dashboard/assign/${slot}/${projectId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Erreur serveur");
            }
            window.location.reload(); // ou Turbo.visit(location.href) si Turbo réactivé
        })
        .catch(error => {
            console.error("Échec de l'assignation :", error);
        });
    }
}
