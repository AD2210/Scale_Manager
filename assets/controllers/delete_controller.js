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

            const data = await response.json()

            if (response.ok && data.success) {
                this.element.closest('tr').remove()
                document.dispatchEvent(new CustomEvent('toast:success', {
                    detail: { message: 'Élément supprimé avec succès' }
                }))
            } else if (response.status === 409 || (data.error && data.error.includes('constraint'))) {
                // Cas spécifique : contrainte SQL (élément lié)
                document.dispatchEvent(new CustomEvent('toast:warning', {
                    detail: {
                        message: 'Impossible de supprimer cet élément car il est lié à d\'autres éléments'
                    }
                }))
            } else {
                throw new Error(data.error || 'Échec de la suppression')
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error)
            document.dispatchEvent(new CustomEvent('toast:error', {
                detail: { message: 'Erreur lors de la suppression' }
            }))
        }
    }
}
