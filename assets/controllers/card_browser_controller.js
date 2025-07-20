import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["card"];

    connect() {
        this.index = 0;
        this.cards = this.cardTargets;
        this.showCard();
    }

    next() {
        if (this.index < this.cards.length - 1) {
            this.index++;
            this.showCard();
        }
    }

    previous() {
        if (this.index > 0) {
            this.index--;
            this.showCard();
        }
    }

    shuffle() {
        for (let i = this.cards.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            this.cards[i].parentNode.insertBefore(this.cards[j], this.cards[i]);
        }
        this.cards = this.cardTargets;
        this.index = 0;
        this.showCard();
    }

    showCard() {
        this.cards.forEach((card, i) => {
            card.classList.toggle('d-none', i !== this.index);
        });
    }
}
