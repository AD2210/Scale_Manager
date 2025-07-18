import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        id: Number,
        type: String
    }

    async delete(event) {
        event.preventDefault()

        if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
            return
        }

        try {
            const response = await fetch(`/generic/delete/${this.typeValue}/${this.idValue}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })

            if (!response.ok) {
                throw new Error('Échec de la suppression')
            }

            const data = await response.json()
            if (data.success) {
                // On supprime la ligne du tableau
                this.element.closest('tr').remove()
                this.dispatch('success', { detail: data })
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error)
            this.dispatch('error', { detail: { message: error.message } })
        }
    }
}
