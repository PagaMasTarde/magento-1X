module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            rename: {
                command:
                    'cp DigitalOrigin_Pmt.tgz DigitalOrigin_Pmt-$(git rev-parse --abbrev-ref HEAD).tgz \n'
            },
            package: {
                command:
                    'cp extension/var/connect/package.xml . \n'
            }
        },
        compress: {

        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.registerTask('default', [
        'shell:package',
        'compress',
        'shell:rename'
    ]);
};