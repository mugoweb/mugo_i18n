;(function ( $, window, document, undefined )
{

	var pluginName = 'mugo_i18n',
	    pluginElement = null,
		defaults = {
			save_service_url : '',
			main_service_url : ''
		};

	function Plugin( element, options )
	{
		// #main-art-hole
		pluginElement  = element;
		this.element   = element;
		this.options   = $.extend( {}, defaults, options) ;
		this._defaults = defaults;
		this._name     = pluginName;

		this.init();
	}
	
	function post_save( data )
	{
		// my editor freaks out if I chain those...
		var li = $( pluginElement ).find( 'input[data-dirty = 1]' ).closest( 'li' );
		li.css( 'background-color', '' );
		li.removeClass( 'unfinished' ).addClass( 'finished' );
	}
	
	Plugin.prototype =
	{
		init : function()
		{
			var self = this;
		
			// dropdowns for extension and locale
			$( '#localelist, #extensionlist' ).change( function(e)
			{
				if( $( '#localelist' ).val() !== '---' && $( '#extensionlist' ).val() !== '---' )
				{
					window.location.href = self.options.main_service_url + '/' + $( '#extensionlist' ).val() + '/' + $( '#localelist' ).val();
				}
			});

			// mark input fields dirty if user changes them
			$( self.element ).find( 'input' ).keydown( function(e)
			{
				if( $(this).attr( 'data-dirty' ) !== '1' )
				{
					$(this).attr( 'data-dirty', '1' );
					$(this).closest( 'li' ).css( 'background-color', '#E0E000' );
				}
			});
						
			// checkbox to hide show finished translations
			$( '#hidefinished' ).click( function(e)
			{
				if( $(this).prop('checked') )
				{
					$( self.element ).find( 'li.finished' ).fadeOut( 'fast' );
				}
				else
				{
					$( self.element ).find( 'li.finished' ).fadeIn( 'fast' );
				}
			});
			
			// translation context filter
			$( self.element ).find( 'fieldset.context legend' ).each( function()
			{
				$( '#contextlist' ).append(
						$('<option>', { value: $(this).attr( 'id' ), text : $(this).html()  })
				);
			});
			$( '#contextlist' ).change( function()
			{
				if( $(this).val() )
				{
					$( self.element ).find( 'fieldset.context' ).hide();
					$( '#' + $(this).val() ).parent().show();
				}
				else
				{
					$( self.element ).find( 'fieldset.context' ).show();
				}
			});
			
			
			// Save button - probably should do it automatically
			$( '#save' ).click( function(e)
			{
				// collect dirty data
				var data =
				{
					locale    : $( '#localelist option:selected' ).val(),
					extension : $( '#extensionlist option:selected' ).val(),
					ids       : [],
					values    : [],
				};

				$( self.element ).find( 'input[data-dirty = 1]' ).each( function()
				{
					data.ids.push( $(this).attr( 'data-id' ) );
					data.values.push( $(this).val() );
				});
				
				$.ajax({
					type    : 'POST',
					url     : self.options.save_service_url,
					data    : data,
					success : post_save
				});
			});
		}
	};

	$.fn[pluginName] = function ( options ) {
		var args = arguments;

		if (options === undefined || typeof options === 'object') {
			return this.each(function ()
			{
				if (!$.data(this, 'plugin_' + pluginName)) {

					$.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
				}
			});

		} else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {

			var returns;

			this.each(function () {
				var instance = $.data(this, 'plugin_' + pluginName);

				if (instance instanceof Plugin && typeof instance[options] === 'function') {

					returns = instance[options].apply( instance, Array.prototype.slice.call( args, 1 ) );
				}

				// Allow instances to be destroyed via the 'destroy' method
				if (options === 'destroy') {
					$.data(this, 'plugin_' + pluginName, null);
				}
			});

			return returns !== undefined ? returns : this;
		}
	};

}(jQuery, window, document));