// assets/controllers/gamme_controller.js
import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'newGlobalPresetName',
        'newTreatmentPresetName',
        'newFinishPresetName',
        'newPrint3DPresetName',
        'treatmentSelect',
        'finishSelect'
    ];

    connect() {
        // On récupère la route actuelle
        this.currentRoute = window.location.pathname;
        this.isPresetRoute = !this.currentRoute.includes('/project/');
    }

    async updateField(event) {
        const field = event.target.dataset.gammeFieldParam;
        let value = event.target.type === 'checkbox' ? event.target.checked : event.target.value;

        if (event.target.tomselect) {
            value = event.target.tomselect.getValue();
        }

        // Si c'est une route preset, on vérifie qu'on a bien un ID
        if (this.isPresetRoute) {
            const presetId = new URLSearchParams(window.location.search).get('id');
            if (!presetId) return; // On n'effectue pas la requête si pas d'ID
        }

        const token = document.querySelector('input[name="token"]').value;

        let url;
        if (!this.isPresetRoute) {
            // Récupérer les IDs du projet et du fichier depuis l'URL
            const matches = this.currentRoute.match(/\/project\/(\d+)\/file.*?/);
            if (!matches) return;

            const projectId = matches[1];
            const currentIndex = new URLSearchParams(window.location.search).get('index') || '0';

            // Récupérer le modèle actuel
            const model = document.querySelector('[data-model-id]');
            const fileId = model ? model.dataset.modelId : null;

            if (!fileId) {
                console.error('ID du modèle non trouvé');
                return;
            }
            url = `/gamme/api/project/${projectId}/file/${fileId}/update`;
        } else {
            const presetId = new URLSearchParams(window.location.search).get('id');
            url = `/gamme/api/preset/${presetId}/update`;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                },
                body: JSON.stringify({field, value})
            });

            if (!response.ok) {
                const error = await response.json();
                console.error('Erreur lors de la mise à jour:', error);
            }
        } catch (error) {
            console.error('Erreur lors de la requête:', error);
        }

    }

    async loadPrint3DPreset(event) {
        if (!event.target.value) return;

        const response = await fetch(`/gamme/api/preset/print3d/${event.target.value}/load`);
        if (response.ok) {
            const data = await response.json();

            // Mettre à jour le preset dans le modèle
            await this.updateField({
                target: {
                    dataset: { gammeFieldParam: 'print3dPreset' },
                    value: event.target.value
                }
            });

            // Mise à jour des champs
            for (const [field, value] of Object.entries(data)) {
                if (value) {
                    const element = document.querySelector(`[data-gamme-field-param="${field}"]`);
                    if (element) {
                        element.value = value;
                        // Déclencher la mise à jour pour chaque champ
                        await this.updateField({
                            target: {
                                dataset: { gammeFieldParam: field },
                                value: value
                            }
                        });
                    }
                }
            }
        }
    }

    async loadTreatmentPreset(event) {
        if (!event.target.value) return;
        try {
            const response = await fetch(`/gamme/api/preset/treatment/${event.target.value}/load`);
            if (response.ok) {
                const data = await response.json();

                // Mettre à jour le preset dans le modèle
                await this.updateField({
                    target: {
                        dataset: { gammeFieldParam: 'treatmentPreset' },
                        value: event.target.value
                    }
                });

                if (this.treatmentSelectTarget) {
                    const autocompleteController = this.application.getControllerForElementAndIdentifier(
                        this.treatmentSelectTarget,
                        'symfony--ux-autocomplete--autocomplete'
                    );

                    if (autocompleteController) {
                        autocompleteController.tomSelect.clear();
                        data.processes.forEach(process => {
                            autocompleteController.tomSelect.addOption({
                                value: process.value,
                                text: process.text
                            });
                            autocompleteController.tomSelect.addItem(process.value);
                        });

                        // Mettre à jour les opérations dans le modèle
                        await this.updateField({
                            target: {
                                dataset: { gammeFieldParam: 'treatmentOperations' },
                                value: data.processes.map(p => p.value)
                            }
                        });
                    }
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement du preset:', error);
        }
    }

    async loadFinishPreset(event) {
        if (!event.target.value) return;

        try {
            // Mettre à jour le preset dans le modèle
            await this.updateField({
                target: {
                    dataset: { gammeFieldParam: 'finishPreset' },
                    value: event.target.value
                }
            });

            const response = await fetch(`/gamme/api/preset/finish/${event.target.value}/load`);
            if (response.ok) {
                const data = await response.json();
                if (this.finishSelectTarget) {
                    const autocompleteController = this.application.getControllerForElementAndIdentifier(
                        this.finishSelectTarget,
                        'symfony--ux-autocomplete--autocomplete'
                    );

                    if (autocompleteController) {
                        // Mettre à jour les valeurs sélectionnées
                        autocompleteController.tomSelect.clear();
                        data.processes.forEach(process => {
                            autocompleteController.tomSelect.addOption({
                                value: process.value,
                                text: process.text
                            });
                            autocompleteController.tomSelect.addItem(process.value);
                        });

                        // Mettre à jour les opérations dans le modèle
                        await this.updateField({
                            target: {
                                dataset: { gammeFieldParam: 'finishOperations' },
                                value: data.processes.map(p => p.value)
                            }
                        });
                    }
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement du preset de finition:', error);
        }
    }


    async loadGlobalPreset(event) {
        if (!event.target.value) return;

        try {
            // Mettre à jour le preset global dans le modèle
            await this.updateField({
                target: {
                    dataset: { gammeFieldParam: 'globalPreset' },
                    value: event.target.value
                }
            });

            const response = await fetch(`/gamme/api/preset/global/${event.target.value}/load`);
            if (response.ok) {
                const data = await response.json();

                // Mettre à jour les sélecteurs de presets
                for (const [type, presetId] of Object.entries({
                    'print3d': data.print3dPreset,
                    'treatment': data.treatmentPreset,
                    'finish': data.finishPreset
                })) {
                    if (presetId) {
                        const select = document.querySelector(`select[data-gamme-field-param="${type}Preset"]`);
                        if (select) {
                            select.value = presetId;
                            // Déclencher le chargement du preset correspondant
                            const event = new Event('change');
                            select.dispatchEvent(event);
                        }
                    }
                }

                // Mettre à jour tous les champs
                if (data.print3dPreset) {
                    await this.loadPrint3DPresetData(data.print3dPreset);
                }
                if (data.treatmentPreset) {
                    await this.loadTreatmentPresetData(data.treatmentPreset);
                }
                if (data.finishPreset) {
                    await this.loadFinishPresetData(data.finishPreset);
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement du preset global:', error);
        }
    }

    // Méthode utilitaire pour mettre à jour les valeurs des selects
    updateSelectValue(type, value) {
        const select = document.querySelector(`select[data-action="gamme#load${type.charAt(0).toUpperCase() + type.slice(1)}Preset"]`);
        if (select) {
            select.value = value || '';
        }
    }

    // Méthodes pour charger les données des sous-presets
    async loadPrint3DPresetData(presetId) {
        // Créer un événement synthétique
        const event = {
            target: document.querySelector('select[data-gamme-field-param="print3dPreset"]')
        };

        // Définir la valeur du select
        if (event.target) {
            event.target.value = presetId;
            // Appeler la méthode loadPrint3DPreset avec l'événement synthétique
            await this.loadPrint3DPreset(event);
        }
    }

    async loadTreatmentPresetData(presetId) {
        const response = await fetch(`/gamme/api/preset/treatment/${presetId}/load`);
        if (response.ok) {
            const data = await response.json();
            if (this.treatmentSelectTarget) {
                const autocompleteController = this.application.getControllerForElementAndIdentifier(
                    this.treatmentSelectTarget,
                    'symfony--ux-autocomplete--autocomplete'
                );

                if (autocompleteController) {
                    autocompleteController.tomSelect.clear();
                    data.processes.forEach(process => {
                        autocompleteController.tomSelect.addOption({
                            value: process.value,
                            text: process.text
                        });
                    });
                    // Ajouter tous les éléments d'un coup
                    autocompleteController.tomSelect.addItems(data.processes.map(p => p.value));
                }
            }
        }
    }

    async loadFinishPresetData(presetId) {
        const response = await fetch(`/gamme/api/preset/finish/${presetId}/load`);
        if (response.ok) {
            const data = await response.json();
            if (this.finishSelectTarget) {
                const autocompleteController = this.application.getControllerForElementAndIdentifier(
                    this.finishSelectTarget,
                    'symfony--ux-autocomplete--autocomplete'
                );

                if (autocompleteController) {
                    autocompleteController.tomSelect.clear();
                    data.processes.forEach(process => {
                        autocompleteController.tomSelect.addOption({
                            value: process.value,
                            text: process.text
                        });
                    });
                    // Ajouter tous les éléments d'un coup
                    autocompleteController.tomSelect.addItems(data.processes.map(p => p.value));
                }
            }
        }
    }

// Ajout de la méthode manquante pour Print3D
    async savePrint3DPreset(event) {
        const name = this.newPrint3DPresetNameTarget.value;
        const selectedPresetId = document.querySelector('select[data-gamme-field-param="print3dPreset"]').value;

        // Si pas de nom mais un preset sélectionné, on met à jour le preset existant
        if (!name && !selectedPresetId) return;

        const token = document.querySelector('input[name="token"]').value;
        const body = {
            process: document.querySelector('[data-gamme-field-param="print3dProcess"]')?.value,
            material: document.querySelector('[data-gamme-field-param="print3dMaterial"]')?.value,
            profil: document.querySelector('[data-gamme-field-param="slicerProfil"]')?.value
        };

        // Si on a un nom, on l'ajoute au body
        if (name) {
            body.name = name;
        }

        const url = selectedPresetId && !name
            ? `/gamme/api/preset/print3d/${selectedPresetId}/update`
            : '/gamme/api/preset/save-print3d';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            body: JSON.stringify(body)
        });

        if (response.ok) {
            //window.location.reload();
        }
    }

    async saveTreatmentPreset(event) {
        const name = this.newTreatmentPresetNameTarget.value;
        const selectedPresetId = document.querySelector('select[data-gamme-field-param="treatmentPreset"]').value;

        if (!name && !selectedPresetId) return;

        const token = document.querySelector('input[name="token"]').value;
        const processes = Array.from(document.querySelector('[name="treatment_process_autocomplete[]"]').selectedOptions)
            .map(option => option.value);

        const url = selectedPresetId && !name
            ? `/gamme/preset/treatment/${selectedPresetId}/update`
            : '/gamme/preset/treatment/save';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            body: JSON.stringify({
                name: name || undefined,
                processes: processes
            })
        });

        if (response.ok) {
            //window.location.reload();
        }
    }

    async saveFinishPreset(event) {
        const name = this.newFinishPresetNameTarget.value;
        const selectedPresetId = document.querySelector('select[data-gamme-field-param="finishPreset"]').value;

        if (!name && !selectedPresetId) return;

        const token = document.querySelector('input[name="token"]').value;
        const processes = Array.from(document.querySelector('[name="finish_process_autocomplete[]"]').selectedOptions)
            .map(option => option.value);

        const url = selectedPresetId && !name
            ? `/gamme/preset/finish/${selectedPresetId}/update`
            : '/gamme/preset/finish/save';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            body: JSON.stringify({
                name: name || undefined,
                processes: processes
            })
        });

        if (response.ok) {
            //window.location.reload();
        }
    }

    async saveGlobalPreset(event) {
        const name = this.newGlobalPresetNameTarget.value;
        const selectedPresetId = document.querySelector('select[data-action="gamme#loadGlobalPreset"]').value;

        if (!name && !selectedPresetId) return;

        const token = document.querySelector('input[name="token"]').value;
        const print3dPreset = document.querySelector('select[data-gamme-field-param="print3dPreset"]')?.value;
        const treatmentPreset = document.querySelector('select[data-gamme-field-param="treatmentPreset"]')?.value;
        const finishPreset = document.querySelector('select[data-gamme-field-param="finishPreset"]')?.value;

        const url = selectedPresetId && !name
            ? `/gamme/preset/global/${selectedPresetId}/update`
            : '/gamme/preset/global/save';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            body: JSON.stringify({
                name: name || undefined,
                print3dPresetId: print3dPreset || null,
                treatmentPresetId: treatmentPreset || null,
                finishPresetId: finishPreset || null
            })
        });

        if (response.ok) {
            //window.location.reload();
        } else {
            const errorData = await response.json();
            console.error('Erreur lors de la sauvegarde:', errorData);
        }
    }

    async handleTomSelectChange(event) {
        await this.updateField({
            target: {
                dataset: { gammeFieldParam: event.target.dataset.gammeFieldParam },
                tomselect: event.target.tomselect,
                value: event.target.tomselect.getValue()
            }
        });
    }

}
