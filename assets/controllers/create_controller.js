import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['form']
    static values = {
        type: String,
        reloadDelay: { type: Number, default: 1500 } // délai en ms avant rechargement
    }

    async create(event) {
        event.preventDefault()

        const form = this.formTarget
        const formData = new FormData(form)

        try {
            const response = await fetch(`/generic/create/${this.typeValue}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })

            const data = await response.json()

            if (response.ok && data.success) {
                // Afficher le toast de succès
                document.dispatchEvent(new CustomEvent('toast:success', {
                    detail: { message: 'Élément créé avec succès' }
                }))

                // Recharger la page après le délai configuré
                setTimeout(() => {
                    window.location.reload()
                }, this.reloadDelayValue)
            } else {
                throw new Error(data.error || 'Erreur lors de la création')
            }
        } catch (error) {
            console.error('Erreur:', error)
            document.dispatchEvent(new CustomEvent('toast:error', {
                detail: { message: error.message }
            }))
        }
    }
}
