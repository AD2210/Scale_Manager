// assets/controllers/dropdown_controller.js
import { Controller } from '@hotwired/stimulus'
import { Dropdown } from 'bootstrap'

export default class extends Controller {
    connect() {
        // Initialise tous les dropdowns Bootstrap dans le conteneur
        const dropdownElements = this.element.querySelectorAll('[data-bs-toggle="dropdown"]')
        dropdownElements.forEach(element => {
            new Dropdown(element)
        })
    }
}
