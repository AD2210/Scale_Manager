import { startStimulusApp } from '@symfony/stimulus-bridge';

// Registre tous les contrôleurs automatiquement
export const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.[jt]sx?$/
));
