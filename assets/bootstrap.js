import { startStimulusApp } from '@symfony/stimulus-bridge';
import { session } from '@hotwired/turbo';
session.drive = false;

// Registre tous les contrôleurs automatiquement
export const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.[jt]sx?$/
));


