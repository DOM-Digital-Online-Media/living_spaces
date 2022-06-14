module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    sass: {
      dist: {
        options: {
          compress: false,
          style: 'expanded',
        },
        files: {
          'css/styles.css': 'scss/styles.scss',
        },
      },
    },
    cssmin: {
      options: {
        // Remove after active theme development.
        sourceMap: true,
      },
      css: {
        src: 'css/styles.css',
        dest: 'css/styles.min.css'
      }
    },
    postcss: {
      options: {
        map: true,
        processors: [
          require('autoprefixer')({
            overrideBrowserslist: ['last 2 versions'],
            cascade: false
          })
        ]
      },
      dist: {
        src: 'css/styles.css',
        dest: 'css/styles.css'
      }
    },
    watch: {
      css: {
        files: '**/*.scss',
        tasks: ['sass', 'postcss', 'cssmin'],
        options: {
          interval: 1000,
        },
      },
    },
    uglify: {
      bootstrap: {
        files: {
          'js/bootstrap.min.js': ['node_modules/bootstrap/dist/js/bootstrap.bundle.js'],
        },
      },
    },
    copy: {
      main: {
        files: [
          {
            expand: true,
            flatten: true,
            src: ['node_modules/bootstrap-icons/font/fonts/*'],
            dest: 'fonts/',
            filter: 'isFile'
          }
        ]
      }
    }
  });
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-postcss');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.registerTask('default', ['copy', 'sass', 'postcss', 'cssmin', 'uglify']);
}
