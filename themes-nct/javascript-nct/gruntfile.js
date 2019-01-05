module.exports = function(grunt){
	grunt.initConfig({	
		concat:{
			options:{
				separator:"\n\n//------------------------------------------------------\n",
				banner:"\n\n//------------------------------------------------------\n"
			},
			dist:{
				src:[
				"jquery.min.js",
				"bootstrap.js",
				"plugins-nct/hello/demos/client_ids.js",
				"plugins-nct/hello/src/hello.js",
				"plugins-nct/hello/src/modules/facebook.js",
				"plugins-nct/hello/src/modules/google.js",
				"plugins-nct/hello/src/modules/linkedin.js",
				"plugins-nct/hello/src/modules/linkedin.js",
				"plugins-nct/bootstrap-select/dist/js/bootstrap-select.min.js",
				"placeholder.js",				
				"bootstrap-nct/datepicker/js/bootstrap-datepicker.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/jquery.form-validator.min.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/location.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/date.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/security.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/file.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/senitize.js",
				"plugins-nct/jQuery-Form-Validator-master/form-validator/logic.js",

				"wow.min.js",
				"custom.js",
				],
				dest:'dist/nct-bundle.js'//destination of combined script
			},//distribution instruction
			dist2:{
				src:[
				"plugins-nct/toggle/bootstrap-toggle.min.js",
				"modules/account_settings-nct.js"
				],
				dest:'dist/account_settings-nct.js'//destination of combined script
			},//distribution instruction
			dist3:{
				src:[
				"plugins-nct/scroll/jquery.mCustomScrollbar.concat.min.js",
				"modules/bid-nct.js"
				],
				dest:'dist/bid-nct.js'//destination of combined script
			},//distribution instruction
			dist4:{
				src:[
				"plugins-nct/scroll/jquery.mCustomScrollbar.concat.min.js",
				"modules/bid-nct.js"
				],
				dest:'dist/bid-nct.js'
			},
			dist5:{
				src:[
				"plugins-nct/scroll/jquery.mCustomScrollbar.concat.min.js",
				"modules/profile-nct.js"
				],
				dest:'dist/profile-nct.js'
			},
			dist6:{
				src:[
				"plugins-nct/tagmanager/tagmanager.js",
				"plugins-nct/typeahead/typeahead.bundle.js",
				"cropper.min.js",
				"modules/edit_profile-nct.js"
				],
				dest:'dist/edit_profile-nct.js'
			},
			dist7:{
				src:[
				"plugins-nct/tagmanager/tagmanager.js",					
				"plugins-nct/typeahead/typeahead.bundle.js",
				"plugins-nct/bootstrap-datepicker/bootstrap-datepicker.min.js",
				"plugins-nct/blueimp/js/vendor/jquery.ui.widget.js",
				"plugins-nct/blueimp/js/tmpl.min.js",
				"plugins-nct/blueimp/js/load-image.all.min.js",
				"plugins-nct/blueimp/js/canvas-to-blob.min.js",
				"plugins-nct/blueimp/js/jquery.iframe-transport.js",
				"plugins-nct/blueimp/js/jquery.fileupload.js",
				"plugins-nct/blueimp/js/jquery.fileupload-process.js",
				"plugins-nct/blueimp/js/jquery.fileupload-image.js",
				"plugins-nct/blueimp/js/jquery.fileupload-audio.js",
				"plugins-nct/blueimp/js/jquery.fileupload-video.js",
				"plugins-nct/blueimp/js/jquery.fileupload-validate.js",
				"plugins-nct/blueimp/js/jquery.fileupload-ui.js",	
				"modules/edit_project-nct.js"
				],
				dest:'dist/edit_project-nct.js'
			},
			dist8:{
				src:[
				"modules/my_providers-nct.js"
				],
				dest:'dist/my_providers-nct.js'
			},
			dist9:{
				src:[
				"plugins-nct/tagmanager/tagmanager.js",					
				"plugins-nct/typeahead/typeahead.bundle.js",
				"plugins-nct/bootstrap-datepicker/bootstrap-datepicker.min.js",
				"plugins-nct/blueimp/js/vendor/jquery.ui.widget.js",
				"plugins-nct/blueimp/js/tmpl.min.js",
				"plugins-nct/blueimp/js/load-image.all.min.js",
				"plugins-nct/blueimp/js/canvas-to-blob.min.js",
				"plugins-nct/blueimp/js/jquery.iframe-transport.js",
				"plugins-nct/blueimp/js/jquery.fileupload.js",
				"plugins-nct/blueimp/js/jquery.fileupload-process.js",
				"plugins-nct/blueimp/js/jquery.fileupload-image.js",
				"plugins-nct/blueimp/js/jquery.fileupload-audio.js",
				"plugins-nct/blueimp/js/jquery.fileupload-video.js",
				"plugins-nct/blueimp/js/jquery.fileupload-validate.js",
				"plugins-nct/blueimp/js/jquery.fileupload-ui.js",	
				"modules/post_project-nct.js"
				],
				dest:'dist/post_project-nct.js'
			},
			dist10:{
				src:[
				"modules/profile-nct.js"
				],
				dest:'dist/profile-nct.js'
			},
			dist11:{
				src:[
				"plugins-nct/scroll/jquery.mCustomScrollbar.concat.min.js",
				"plugins-nct/bootstrap-datepicker/bootstrap-datepicker.min.js",
				"plugins-nct/blueimp/js/vendor/jquery.ui.widget.js",
				"plugins-nct/blueimp/js/tmpl.min.js",
				"plugins-nct/blueimp/js/load-image.all.min.js",
				"plugins-nct/blueimp/js/canvas-to-blob.min.js",
				"plugins-nct/blueimp/js/jquery.iframe-transport.js",
				"plugins-nct/blueimp/js/jquery.fileupload.js",
				"plugins-nct/blueimp/js/jquery.fileupload-process.js",
				"plugins-nct/blueimp/js/jquery.fileupload-image.js",
				"plugins-nct/blueimp/js/jquery.fileupload-audio.js",
				"plugins-nct/blueimp/js/jquery.fileupload-video.js",
				"plugins-nct/blueimp/js/jquery.fileupload-validate.js",
				"plugins-nct/blueimp/js/jquery.fileupload-ui.js",	
				"plugins-nct/rating/js/star-rating.min.js",	
				"modules/project_detail-nct.js"
				],
				dest:'dist/project_detail-nct.js'
			},
			dist12:{
				src:[
				"plugins-nct/tagmanager/tagmanager.js",					
				"plugins-nct/typeahead/typeahead.bundle.js",
				"plugins-nct/bootstrap-datepicker/bootstrap-datepicker.min.js",
				"plugins-nct/blueimp/js/vendor/jquery.ui.widget.js",
				"plugins-nct/blueimp/js/tmpl.min.js",
				"plugins-nct/blueimp/js/load-image.all.min.js",
				"plugins-nct/blueimp/js/canvas-to-blob.min.js",
				"plugins-nct/blueimp/js/jquery.iframe-transport.js",
				"plugins-nct/blueimp/js/jquery.fileupload.js",
				"plugins-nct/blueimp/js/jquery.fileupload-process.js",
				"plugins-nct/blueimp/js/jquery.fileupload-image.js",
				"plugins-nct/blueimp/js/jquery.fileupload-audio.js",
				"plugins-nct/blueimp/js/jquery.fileupload-video.js",
				"plugins-nct/blueimp/js/jquery.fileupload-validate.js",
				"plugins-nct/blueimp/js/jquery.fileupload-ui.js",	
				"modules/repost_project-nct.js"
				],
				dest:'dist/repost_project-nct.js'
			},
			dist13:{
				src:[
				"plugins-nct/ionRangeSlider/js/ion.rangeSlider.js",					
				"modules/search_projects-nct.js"
				],
				dest:'dist/search_projects-nct.js'
			},
			dist14:{
				src:[
				"plugins-nct/ionRangeSlider/js/ion.rangeSlider.js",					
				"modules/search_providers-nct.js"
				],
				dest:'dist/search_providers-nct.js'
			},
		},
		uglify: {
			options: {
		    // the banner is inserted at the top of the output
		    banner: '\n\n//----------- File uglified by Ashish Joshi -----------------\n'
			},
			dist: {
				files: {
					'dist/nct-bundle.js': 'dist/nct-bundle.js'
				}
			},
			
		},
		cssmin: {
			options: {
				mergeIntoShorthands: false,
				roundingPrecision: -1
			},
			dist: {
				files:{ '../css-nct/nct-bundle.css' :[
					'../css-nct/toastr.min.css', 
					'../css-nct/bootstrap.min.css', 
					'../css-nct/animate.css', 
					'../css-nct/style.css', 
					'../css-nct/responsive.css', 
					'../css-nct/child.css', 
					'plugins-nct/bootstrap-select/dist/css/bootstrap-select.min.css', 
					'../bootstrap-nct/datepicker/css/datepicker.css', 
					'../css-nct/style-footer-nct.css',
					'../css-nct/font-awesome.min.css',
				]}
			}
		},
		imagemin: {                          // Task
    
		    dynamic: {                         // Another target
		      files: [{
		        expand: true,  
		        optimizationLevel: 6,                // Enable dynamic expansion
		        cwd: '../images-nct/',                   // Src matches are relative to this path
		        src: ['**/*.{png,jpg,gif}'],   // Actual patterns to match
		        dest: '../images-nct/'                  // Destination path prefix
		      }]
		    }
		  },

});
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-imagemin');
	grunt.registerTask("default",[
		"concat:dist",
		"uglify:dist", 
		"concat:dist2", 
		"concat:dist3",
		"concat:dist4",
		"concat:dist5",
		"concat:dist6",
		"concat:dist7",
		"concat:dist8",
		"concat:dist9",
		"concat:dist10",
		"concat:dist11",
		"concat:dist12",
		"concat:dist13",
		"concat:dist14",
		"cssmin:dist",
		//"imagemin"
		]);

};