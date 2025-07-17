import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        id: Number,
        type: String,
        field: String
    }

    connect() {
        // console.log(`Stimulus editable ready for ${this.typeValue}:${this.fieldValue}`)
    }

    update(event) {
        const target = event.target
        let value = null

        if (target.type === 'checkbox') {
            if (target.closest('.dropdown-menu')) {
                // Multi-select case (checkbox in dropdown)
                const checkboxes = target
                    .closest('td')
                    .querySelectorAll('input[type="checkbox"]:checked')
                value = Array.from(checkboxes).map(cb => cb.value)
            } else {
                // Single boolean checkbox
                value = target.checked
            }
        } else {
            value = target.value
        }

        const payload = {}
        payload[this.fieldValue] = value

        fetch(`/generic/update/${this.typeValue}/${this.idValue}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
            .then((response) => {
                if (!response.ok) throw new Error('Échec de la mise à jour.')
                return response.json()
            })
            .then((data) => {
                // Optionnel : retour visuel ou console
                // console.log('Mise à jour réussie', data)
            })
            .catch((err) => {
                console.error(err)
                alert('Erreur lors de la mise à jour')
            })
    }
}
