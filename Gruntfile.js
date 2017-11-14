module.exports = function(grunt) {
    grunt.initConfig({
        shell: {
            rename: {
                command:
                    'cp extension/var/connect/DigitalOrigin_Pmt.tgz extension/var/connect/DigitalOrigin_Pmt-$(git rev-parse --abbrev-ref HEAD).tgz \n'
            }
        },
        compress: {
            main: {
                options: {
                    archive: 'extension/var/connect/DigitalOrigin_Pmt.tgz',
                    mode: 'tgz'
                },
                files: [
                    {expand: 'true', cwd:'extension/', src: ['app/**'], dest: '/', filter: 'isFile'},
                    {expand: 'true', cwd:'extension/', src: ['lib/**'], dest: '/', filter: 'isFile'},
                    {expand: 'true', cwd:'extension/var/connect/', src: ['package.xml'], dest: '/', filter: 'isFile'},
                ]

            }
        }
    });

    grunt.loadNpmTasks('grunt-shell');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.registerTask('default', [
        'compress',
        'shell:rename'
    ]);
};
