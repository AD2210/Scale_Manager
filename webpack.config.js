const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableSassLoader((options) => {
        options.sassOptions = {
            quietDeps: true,
            silenceDeprecations: ['import', 'color-functions', 'global-builtin', 'mixed-decls']
        };
    })
    .enableStimulusBridge('./assets/controllers.json')
    .autoProvidejQuery()

    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins = [];
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

;

module.exports = Encore.getWebpackConfig();
