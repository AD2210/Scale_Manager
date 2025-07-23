import {Controller} from '@hotwired/stimulus'

export default class extends Controller {
    static values = {
        id: Number,
        type: String,
        field: String
    }

    update(event) {
        const target = event.target
        let options, url, value

        // Récupération des données additionnelles si présentes
        const additionalData = {}
        const td = target.closest('td')
        if (td.dataset.additionalData) {
            try {
                Object.assign(additionalData, JSON.parse(td.dataset.additionalData))
            } catch (e) {
                console.error('Erreur parsing additionalData:', e)
            }
        }

        // Détermination de la valeur selon le type d'input
        if (target.closest('.dropdown-menu') && target.type === 'checkbox') {
            // Cas des dropdowns à choix multiple
            const checkboxes = target
                .closest('.dropdown-menu')
                .querySelectorAll('input[type="checkbox"]:checked')
            value = Array.from(checkboxes).map(cb => cb.value)
        } else if (target.type === 'checkbox') {
            value = target.checked
        } else if (target.type === 'file') {
            // Cas des fichiers
            const file = target.files[0]
            if (!file) return

            const formData = new FormData()
            formData.append(this.fieldValue, file)

            options = {
                method: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PATCH',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }
            url = `/generic/update/${this.typeValue}/${this.idValue}`
        } else {
            value = target.value
        }

        // Configuration de la requête selon le type d'opération
        if (!options) {
            if (this.typeValue === 'treatmentOperation' && this.fieldValue === 'isDone') {
                // Cas spécifique pour la mise à jour du isDone des opérations de traitement
                options = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        field: this.fieldValue,
                        value: value,
                        entityId: this.idValue
                    })
                }
                url = `/api/treatment/operation/${target.closest('tr').dataset.id}`
            } else if ((this.typeValue === 'model' && this.fieldValue === 'treatmentProcess')){
                const operationId = target.dataset.operationId || td.dataset.operationId
                options = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        field: this.fieldValue,
                        value: value,
                        operationId: operationId
                    })
                }
                url = `/api/treatment/operation/${this.idValue}`

            } else if (this.typeValue === 'customerData' && this.fieldValue === 'customerDataOperation') {
                // Cas spécifique pour les opérations CustomerData
                options = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        value: value,
                        'software-id': additionalData.softwareId
                    })
                }
                url = `/api/customer-data/${this.idValue}/operation`
            } else {
                // Cas standard pour toutes les autres mises à jour
                options = {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        [this.fieldValue]: value,
                        ...additionalData
                    })
                }
                url = `/generic/update/${this.typeValue}/${this.idValue}`
            }
        }

        fetch(url, options)
            .then(response => {
                if (!response.ok) throw new Error('Échec de la mise à jour')
                return response.json()
            })
            .then(data => {
                if (data.success) {
                    document.dispatchEvent(new CustomEvent('toast:success', {
                        detail: {message: 'Mise à jour effectuée avec succès'}
                    }))

                    // Recharger la page uniquement pour l'ajout d'une nouvelle opération de traitement
                    if (this.typeValue === 'model' && this.fieldValue === 'treatmentProcess') {
                        window.location.reload()
                    }
                }
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour:', error)
                document.dispatchEvent(new CustomEvent('toast:error', {
                    detail: {message: 'Erreur lors de la mise à jour'}
                }))
            })
    }
}
