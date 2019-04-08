<?php
class Base_Text
{
    public static $init = false;
    public $id;

    public static function init()
    {
        if (self::$init) {
            return false;
        }

        $uploadLink = Base_Config::getUploadImageLink();
        return "
        <script>
        if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
	        CKEDITOR.tools.enableHtml5Elements( document );
            CKEDITOR.config.imageUploadUrl = '" . $uploadLink . "';
            CKEDITOR.config.extraPlugins = 'uploadimage';
            CKEDITOR.config.height = 150;
            CKEDITOR.config.width = 'auto';
            CKEDITOR.config.filebrowserBrowseUrl = '/browser/browse.php';
            CKEDITOR.config.filebrowserUploadUrl = '/admin/index/easyUploadFile/';
var wysiwygareaAvailable;
( function() {
	wysiwygareaAvailable = isWysiwygareaAvailable(),
		isBBCodeBuiltIn = !!CKEDITOR.plugins.get( 'bbcode' );
//	return function() {
//		// :(((
//		if ( isBBCodeBuiltIn ) {
//			editorElement.setHtml('Hello world!  I\'m an instance of [url=http://ckeditor.com]CKEditor[/url].'
//			);
//		}
//	};

	function isWysiwygareaAvailable() {
		// If in development mode, then the wysiwygarea must be available.
		// Split REV into two strings so builder does not replace it :D.
		if ( CKEDITOR.revision == ( '%RE' + 'V%' ) ) {
			return true;
		}

		return !!CKEDITOR.plugins.get( 'wysiwygarea' );
	}
} ).call(this);
</script>";
    }

    public function __construct($id)
    {
        $this->setId($id);
    }

    /**
     * @param $data Base_Text
     */
    public function setHtml($data)
    {

        $uploaddir = Base_Config::getSiteDir() . Base_Config::getRtfDirectory();
        $fName = $this->getId()  . '.rtf';
        $fNameReserved = $this->getId()  . '_reserved.rtf';

        if (empty($data)) {
            return false;
        }

        if (file_exists($uploaddir . $fName)) {
            file_put_contents($uploaddir . $fNameReserved, file_get_contents($uploaddir . $fName));
        }

        return file_put_contents($uploaddir . $fName, $data);
    }

    public function renderHtml()
    {
        $uploaddir = Base_Config::getSiteDir() . Base_Config::getRtfDirectory();
        $fName = $this->getId()  . '.rtf';
        if (file_exists($uploaddir . $fName)) {
            return file_get_contents($uploaddir . $fName);
        }
        return '';
    }

    public function renderEditor()
    {
        $rtfLink = Base_Config::getUploadRtfLink();
        $nowData = $this->renderHtml();
        return "
        <script>
        var _editor_". $this->getId() . " = ( function() {
        var editorElement = CKEDITOR.document.getById( '". $this->getId() . "' );
            if ( wysiwygareaAvailable ) {
                CKEDITOR.replace( '". $this->getId() . "' );
            } else {
                editorElement.setAttribute( 'contenteditable', 'true' );
                CKEDITOR.inline( '". $this->getId() . "' );
    
                // TODO we can consider displaying some info box that
                // without wysiwygarea the classic editor may not work.
            }
            CKEDITOR.plugins.registered['save'] = {
              init: function (editor) {
                 var command = editor.addCommand('save',
                 {
                      modes: { wysiwyg: 1, source: 1 },
                      exec: function (editor) { // Add here custom function for the save button
                          var html = editor.getData();
                          var id = '". $this->getId() . "';
                          $.post('" . $rtfLink . "', {html: html, id: id}, function(data){
                            if (data.message) {
                                alert(data.message);
                            }
                          });
                      }
                 });
                 editor.ui.addButton('Save', { label: 'Save', command: 'save' });
              }
            }
		});
		</script>
		<div id=\"". $this->getId() . "\">
					" . $nowData . "
        </div>
        <script>_editor_". $this->getId() . "()</script>
		";
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

}