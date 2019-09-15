<?php

/**
 * Originphp Framework
 *
 * @copyright  Copyright (c) 2011 Johnson Tsang <contactor@gmail.com>
 * @license    https://github.com/contactor/originphp/blob/master/LICENSE-new-bsd.txt     New BSD License
 * @version    2.2.6
 */
namespace origin\mvc;

/**
 * MVC View.
 * @author Johnson Tsang <contactor@gmail.com> 2011-03-12
 */
class View {
    
    /**
     * Use translation method for locale.
     * This option is for both view and php code message locale.
     */
    const TRANSLATION = 1;
    /**
     * Use multi-view method for locale.
     * This option is only for view locale.
     */
    const MULTI_VIEW = 2;
    
    /**
     * view-file extension
     */
    const _VIEW_FILE_EXT = '.phtml';
    
    /**
     * multi-language support
     */
    const _VIEW_LANG_DIR = 'lang/';
    const _VIEW_LANG_EXT = '_lang';
    
    /**
     * multi-language files namespace 
     */
    const _VIEW_LANG_NS = 'lang\\';
    
    /**
     * multi-language translation table index
     */
    const _VIEW_TRANS_TABLE_INDEX = 0;
    const _CODE_TRANS_TABLE_INDEX = 1;
    
    /**
     * multi-language translation table index
     */
    const _HELPER_NS = '\\origin\\plugin\\viewhelper\\';
    
    /**
     * multi-language support
     */
    private $_lang_name = FALSE;
    private $_translation_tables = array(FALSE, FALSE);
    
    /**
     * view mode
     */
    private $_layout_mode = self::MULTI_VIEW;
    private $_content_mode = self::MULTI_VIEW;
    
    /**
     * default content file for view
     * @var string or array
     */
    private $_content_filename;
    
    /**
     * layout view variables
     * @var array
     */
    private $_layout_args;
    
    /**
     * content view variables
     * @var array
     */
    private $_content_args;
    
    /**
     * View root path, format like '/www/project/ui/templates/views'
     * @var string
     */
    private $_view_root_path;
    
    /**
     * View root namespace, format like 'templates\\views'
     * @var string
     */
    private $_view_root_namespace = '';
    
    /**
     * module path under view root path format like '/user/profile/'
     * @var string
     */
    private $_view_path;
    
    /**
     * Event procedure: on prepare view 
     * @var callable
     */
    private $_on_prepare_view;
    
    /**
     * View helper instance cache
     * @var array
     */
    private $_helpers;

    /**
     * View root path, format like '/www/project/ui/templates/views'
     * View root namespace, format like 'templates\\views'
     * @param string $view_root_path
     * @param string $view_root_namespace
     */
    public function setViewRootPath($view_root_path, $view_root_namespace = '') {
        $this->_view_root_path = $view_root_path;
        $this->_view_root_namespace = $view_root_namespace;
    }

    /**
     * Get view path, view path is the module relative path to view root path
     * format like '/user/profile/'
     * @return string
     */
    public function getViewPath() {
        return $this->_view_path;
    }

    /**
     * Get view path, view path is the module relative path to view root path
     * format like '/user/profile/'
     * @param string $view_path
     */
    public function setViewPath($view_path) {
        $this->_view_path = $view_path;
    }

    /**
     * Set on prepare view event.
     * @param callable $on_prepare_view
     */
    public function setOnPrepareViewEvent($on_prepare_view) {
        $this->_on_prepare_view = $on_prepare_view;
    }

    /**
     * Set language name.
     * If language name is not empty, the global locale table 
     * for php code message will automatic load.
     * @param string $lang_name
     */
    public function setLocaleLanguage($lang_name) {
        if (! function_exists('lang')) {
            include 'locale.php';
        }
        
        if (empty($lang_name)) {
            $lang_name = FALSE;
        } elseif (! is_string($lang_name)) {
            throw new \UnexpectedValueException('Invalid locale language name.');
        }
        if ($lang_name == $this->_lang_name) {
            return;
        }
        $this->_lang_name = $lang_name;
        $this->_translation_tables[self::_CODE_TRANS_TABLE_INDEX] = FALSE;
    }

    /**
     * Set view locale mode.
     * @param boolean $layout_translation
     * @param boolean $content_translation
     */
    public function setLocaleMode($layout_translation, $content_translation) {
        $this->_layout_mode = $layout_translation ? self::TRANSLATION : self::MULTI_VIEW;
        $this->_content_mode = $content_translation ? self::TRANSLATION : self::MULTI_VIEW;
    }

    /**
     * Set layout view args.
     * @param array $args
     */
    public function setLayoutArgs($args) {
        $this->_layout_args = $args;
    }

    /**
     * Set content view args.
     * @param array $args
     */
    public function setContentArgs($args) {
        $this->_content_args = $args;
    }

    /**
     * Translate view message by locale message table.
     * @param string $message
     */
    public function translateViewMessage($message) {
        return $this->translateMessage($message, self::_VIEW_TRANS_TABLE_INDEX);
    }

    /**
     * Translate php code message by locale message table.
     * @param string $message
     */
    public function translateCodeMessage($message) {
        return $this->translateMessage($message, self::_CODE_TRANS_TABLE_INDEX);
    }

    /**
     * Add locale message table for php code message translation.
     * The locale table convention:
     * File name is Name + '.' + language name + '.php'
     * Class name is Name + 'Lang'
     * Class contains a public static property named $table
     * @param string $table_name
     */
    public function addTranslationTable($class_path) {
        if ($this->_lang_name) {
            $class_name = $class_path . '_' . $this->_lang_name;
            $this->addToTranslationTable($class_name, self::_CODE_TRANS_TABLE_INDEX);
        }
    }

    /**
     * Render view.
     * @param string $layout_filename
     * @param string or empty $content_filename
     * @param boolean $layout_translation
     * @param boolean $content_translation
     * @param boolean $is_to_string
     * @return string if $is_to_string is TRUE, or NULL
     */
    public function render($layout_filename, $content_filename = FALSE, $layout_translation = FALSE, $content_translation = FALSE, $is_to_string = FALSE) {
        $layout_filename = $this->checkFilename($layout_filename);
        if (! $layout_filename) {
            return NULL;
        }
        $this->_content_filename = $this->checkFilename($content_filename);
        if ($layout_translation || $content_translation) {
            $this->setLocaleMode($layout_translation, $content_translation);
        }
        
        if ($is_to_string) {
            ob_start();
        }
        if (is_callable($this->_on_prepare_view)) {
            call_user_func($this->_on_prepare_view, $this);
        }
        if ($layout_filename) {
            $this->showViewFile($this->getViewFile($layout_filename, TRUE), $this->_layout_args);
        }
        return $is_to_string ? ob_get_clean() : NULL;
    }

    /**
     * Show content view.
     */
    public function content($content_filename = FALSE) {
        $content_filename = $content_filename ? $this->checkFilename($content_filename) : $this->_content_filename;
        if ($content_filename) {
            $this->showViewFile($this->getViewFile($content_filename, FALSE), $this->_content_args);
        }
    }

    /**
     * Add a view helper
     * If the class name has no namespace, the default namespace used
     * @param string $class_name
     * @param string $nickname
     * @param mixed $param
     * @return object
     */
    public function addHelper($class_name, $nickname, $param = NULL) {
        if (strpos($class_name, '\\') === FALSE) {
            $class_name = self::_HELPER_NS . $class_name;
        }
        if (! $this->_helpers) {
            $this->_helpers = array();
        } else {
            if (isset($nickname, $this->_helpers)) {
                $added = TRUE;
            }
        }
        if (empty($added)) {
            $this->_helpers[$nickname] = $param === NULL ? new $class_name() : new $class_name($param);
        }
        return $this->_helpers[$nickname];
    }

    /**
     * Remove a view helper
     * @param string $nickname
     * @return \origin\mvc\View
     */
    public function removeHelper($nickname) {
        if (is_array($this->_helpers)) {
            unset($this->_helpers[$nickname]);
        }
        return $this;
    }

    /**
     * Get view helper
     * @param string $nickname
     */
    public function helper($nickname) {
        if (! isset($nickname, $this->_helpers)) {
            throw new \UnexpectedValueException('view helper not exists: ' . $nickname);
        }
        return $this->_helpers[$nickname];
    }

    private function checkFilename($filename) {
        if (is_string($filename)) {
            return $filename;
        }
        if (empty($filename)) {
            return FALSE;
        }
        throw new \InvalidArgumentException('Invalid view file name');
    }

    private function addToTranslationTable($class_name, $trans_table_index) {
        if ($this->_translation_tables[$trans_table_index] === FALSE) {
            $this->_translation_tables[$trans_table_index] = array();
        } else {
            if (isset($this->_translation_tables[$trans_table_index][$class_name])) {
                return;
            }
        }
        $this->_translation_tables[$trans_table_index][$class_name] = $class_name::$table;
    }

    private function translateMessage($message, $trans_table_index) {
        if ($this->_lang_name && $this->_translation_tables[$trans_table_index]) {
            foreach ($this->_translation_tables[$trans_table_index] as $lang) {
                if (isset($lang[$message])) {
                    return $lang[$message];
                }
            }
        }
        return $message;
    }

    private function getViewFile($view_filename, $is_layout) {
        $locale_mode = $is_layout ? $this->_layout_mode : $this->_content_mode;
        switch ($locale_mode) {
            case self::MULTI_VIEW:
                $lang_dir = $this->_lang_name ? self::_VIEW_LANG_DIR . $this->_lang_name . '/' : '';
                $lang_ns = '';
                break;
            
            case self::TRANSLATION:
                $lang_dir = '';
                $lang_ns = $this->_lang_name ? self::_VIEW_LANG_NS . $this->_lang_name . '\\' : '';
                break;
            
            default:
                throw new \UnexpectedValueException('Invalid locale mode.');
        }
        $module_path = $this->_view_root_path . $this->_view_path;
        $view_file = $module_path . $lang_dir . $view_filename . self::_VIEW_FILE_EXT;
        
        if ($lang_ns) {
            $view_path = str_replace('/', '\\', $this->_view_path);
            $class_name = $this->_view_root_namespace . $view_path . $lang_ns . $view_filename . self::_VIEW_LANG_EXT;
            $this->addToTranslationTable($class_name, self::_VIEW_TRANS_TABLE_INDEX);
        }
        
        return $view_file;
    }

    private function showViewFile($view_filename, $view_params) {
        if (is_array($view_params)) {
            extract($view_params);
        }
        include $view_filename;
    }
}

?>