import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['meaning']

  toggle() {
    this.meaningTarget.classList.toggle('d-none')
  }
}
