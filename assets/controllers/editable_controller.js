import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        id: Number,
        type: String,
        field: String
    }

    update(event) {
        const target = event.target
        let payload, options

        if (target.type === 'file') {
            const formData = new FormData()
            const file = target.files[0]
            if (!file) return

            formData.append(this.fieldValue, file)
            options = {
                method: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PATCH',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }
        } else {
            let value = null

            if (target.type === 'checkbox') {
                if (target.closest('.dropdown-menu')) {
                    const checkboxes = target
                        .closest('td')
                        .querySelectorAll('input[type="checkbox"]:checked')
                    value = Array.from(checkboxes).map(cb => cb.value)
                } else {
                    value = target.checked
                }
            } else {
                value = target.value
            }

            options = {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ [this.fieldValue]: value })
            }
        }

        fetch(`/generic/update/${this.typeValue}/${this.idValue}`, options)
            .then(response => {
                if (!response.ok) throw new Error('Échec de la mise à jour')
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    document.dispatchEvent(new CustomEvent('toast:success', {
                        detail: { message: 'Mise à jour effectuée avec succès' }
                    }))
                }
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour:', error)
                document.dispatchEvent(new CustomEvent('toast:error', {
                    detail: { message: 'Erreur lors de la mise à jour' }
                }))
            })
    }
}
