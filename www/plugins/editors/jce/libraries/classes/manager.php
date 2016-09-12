<?php
/**
 * @version		$Id: manager.php 2007-08-04 09:51:30Z happy_noodle_boy $
 * @package		JCE
 * @copyright	Copyright (C) 2005 - 2007 Ryan Demmer. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

/**
 * Manager class
 *
 * @static
 * @package		JCE
 * @since	1.5
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class Manager extends JContentEditorPlugin{
		/*
		*  @var varchar
		*/
        var $_base = null;
		/*
		*  @var array
		*/
		var $_buttons = array();
		/*
		*  @var array
		*/
		var $_actions = array();
		/*
		*  @var array
		*/
		var $_events = array();
		/*
		*  @var array
		*/
		var $_result = array(
			'error' => ''
		);
		/**
		* @access	protected
		*/
		function __construct(){
			// Call parent
			parent::__construct();
			
			$this->_base 			= $this->getRootDir();
			$this->_plugin->type	= 'manager';

		}
		/**
		 * Returns a reference to a Manager object
		 *
		 * This method must be invoked as:
		 * 		<pre>  $manager = &Manager::getInstance();</pre>
		 *
		 * @access	public
		 * @return	JCE  The editor object.
		 * @since	1.5
		 */
		function &getInstance(){
			static $instance;

			if ( !is_object( $instance ) ){
				$instance = new Manager();
			}
			return $instance;
		}
		/*
        * Initialize the Manager plugin
        * Shortcut to setup Manager elements
        */
		function init(){
			// Setup XHR callback funtions
			$this->setXHR( array( $this, 'getItems' ) );
			$this->setXHR( array( $this, 'getFileDetails' ) );
			$this->setXHR( array( $this, 'getFolderDetails' ) );
			$this->setXHR( array( $this, 'getTree' ) );
			$this->setXHR( array( $this, 'getTreeItem' ) );
			
			// Get actions
			$this->getStdActions();
			// Get buttons
			$this->getStdButtons();
			
			// Store default manager scripts
			$this->script( array( 'tiny_mce_popup' ), 'tiny_mce' );
			$this->script( array( 				
				'tiny_mce_utils',
				'mootools',
				'jce',
				'plugin', 
				'window',
				'listsorter',
				'searchables',
				'tree',
				'upload',
				'manager'
			) );
			
			// Store default manager css
			$this->css( array( 
				'plugin', 
				'manager',
				'upload',
				'tree'
			) );
			$this->css( array( 
				'window',
				'dialog'
			), 'skins' );
			
			// Load language files
			$this->loadLanguages();
		}
		/**
         * Get the relative base directory variable.
         * @return string base dir
         */
		function getBase(){
			return $this->_base;
		}
		/**
         * Get the base directory.
         * @return string base dir
         */
        function getBaseDir(){
			return Utils::makePath( JPATH_ROOT, $this->_base );
        }
        /**
         * Get the full base url
         * @return string base url
         */
        function getBaseURL(){
			return Utils::makePath( $this->_site_url, $this->_base );
        }
		/**
		 * Return the full user directory path. Create if required
		 *
		 * @param string	The base path
		 * @access public
		 * @return Full path to folder
		*/
		function getRootDir(){
			jimport('joomla.filesystem.folder');
			
			// Get base directory as shared parameter
			$base = $this->getSharedParam( 'dir', 'images/stories' );
			
			// Remove first leading slash
			if( substr( $base, 0, 1 ) == '/' ){
				$base = substr( $base, 1 );
			}			
			// Force default directory if base param starts with a variable, eg $id or a slash and variable, eg: /$id
			if( preg_match( '/(\$|\/\$|\.|\/\.)/', substr( $base, 0, 2 ) ) ){
				$base = 'images/stories';
			}			
			// Super Administrators not affected
			if( $this->isSuperAdmin() ){
				// Get the root folder before dynamic variables for Super Admin
				$parts 	= explode( '$', $base );
				$base	= $parts[0];
			}else{
				// Replace any path variables
				$base = preg_replace( array( '/\$id/', '/\$username/', '/\$usertype/', '/\$group/' ), array( $this->_id, $this->_username, $this->_usertype, strtolower( $this->_group->name ) ), $base );
			}
			
			$full = Utils::makePath( JPATH_SITE, $base );
			if( !JFolder::exists( $full ) ){
				$this->folderCreate( $full );
			}
			$base = JFolder::exists( $full ) ? $base : 'images/stories';
			
			// Fallback value
			if( !$base ){
				$base = 'images/stories';
			}
			
			return ( substr( $base, 0, 1) == '/' ) ? substr( $base, 1 ) : $base;
		}
		/**
		 * Set the plugin filetypes
		 * Checks for filetypr group name. Default inserted if not available
		 * @param string	The filetype list, eg: html=html,htm;text=txt
		 * @access public
		*/
		function setFileTypes( $types ){
			if( strpos( $types, '=' ) === false ){
				$types = 'files=' .$types;
			}
			$this->_filetypes = $types;
		}
		/**
		 * Return a list of allowed file extensions in selected format
		 *
		 * @access public
		 * @return extension list
		*/
		function getFileTypes( $format = 'map' ){
			$list = $this->_filetypes;

			switch( $format ){
				case 'list':
					return $this->listFileTypes( $list );
					break;
				case 'array':
					return explode( ',', $this->listFileTypes( $list ) );
					break;
				default:
				case 'map':
					return $list;
					break;
			}
		}
		/**
		* Converts the extensions map to a list
		* @param string $map The extensions map eg: images=jpg,jpeg,gif,png
		* @return string jpg,jpeg,gif,png
		*/
		function listFileTypes( $map ){
			$array = explode( ';', $map );
			$icons = array();
			foreach( $array as $items ){
				$item = explode( '=', $items );
				$icons[] = $item[1];
			}
			return implode( ',', $icons );
		}
		/*
        * Maps upload file types to an upload dialog list, eg: 'images', 'jpeg,jpg,gif,png'
        * @return json encoded list
        */
		function mapUploadFileTypes(){
			// Get the filetype map
			$list 	= $this->getFileTypes();		
			$items 	= explode( ';', $list );
			$map 	= array();
			// [images=jpeg,jpg,gif,png]
			foreach( $items as $item ){
				// ['images', 'jpeg,jpg,gif,png']
				$kv 		= explode( '=', $item );
				$extensions = implode( ';', preg_replace( '/(\w+)/i', '*.$1', explode( ',', $kv[1] ) ) );
				$map[JText::_( $kv[0] ). ' (' .$extensions. ')'] = $extensions;
			}
			// All file types
			$map[JText::_('All Files') . ' (*.*)'] = '*.*';
			return $this->json_encode( $map );
		}
		/*
        * Returns the result variable
        * @return var $_result 
        */
		function returnResult(){
			return $this->_result;
		}
		/**
         * Simulate javascript escape function, ie: escape all but /?@*+
         * @return escaped string
        */
		function jsEscape( $string ){
			// Already escaped? Avoid double escaping
			if( preg_match( '/%([0-9A-Z]+)/', $string ) ){
				return $string;
			}
			return preg_replace( 
				array( '/%2F/', '/%3F/', '/%40/', '/%2A/', '/%2B/' ), 
				array( '/', '?', '@', '*', '+' ), 
			rawurlencode( $string ) );
		}
		/**
         * Get the list of files in a given folder
		 * @param string $relative The relative path of the folder
		 * @param string $filter A regex filter option         
		 * @return File list array
        */
        function getFiles( $relative, $filter='.' ){
			jimport('joomla.filesystem.folder');
			$path = Utils::makePath( $this->getBaseDir(), $relative );
            $list = JFolder::files( $path, $filter );
			if( !empty( $list ) ){
                // Sort alphabetically
				natcasesort( $list );
				for ($i = 0; $i < count( $list ); $i++) {
                    $file = $list[$i];
                    $files[] = array(
						'url' 		=> Utils::makePath( $relative, $file ),
						'name' 		=> $file
                    );
                }
                return $files;
            }else{
                return $list;
            }
        }
		/**
         * Get the list of folder in a given folder
		 * @param string $relative The relative path of the folder        
		 * @return Folder list array
        */
        function getFolders( $relative ){
			jimport('joomla.filesystem.folder');
			$path = Utils::makePath( $this->getBaseDir(), $relative );
            $list = JFolder::folders( $path );
            if( !empty( $list ) ){
                // Sort alphabetically
				natcasesort( $list );
				for ($i = 0; $i < count( $list ); $i++) {
                    $folder = $list[$i];
                    $folders[] = array(
                        'url' 	=> Utils::makePath( $relative, $folder ),
						'name' 	=> $folder
                    );
                }
                return $folders;
            }else{
                return $list;
            }
        }
		/**
         * Get a tree node
		 * @param string $dir The relative path of the folder to search        
		 * @return Tree node array
        */
		function getTreeItem( $dir ){
			$folders 	= $this->getFolders( rawurldecode( $dir ) );
			$array 		= array();
			foreach( $folders as $folder ){
				$array[] = array(
					'id'		=>	$folder['url'],
					'name'		=>	$folder['name'],
					'class'		=>	'folder'
				);
			}
			$result[] = array( 
				'folders'	=>	$array
			);
			return $result;
		}
		/**
         * Build a tree list
		 * @param string $dir The relative path of the folder to search        
		 * @return Tree html string
        */
		function getTree( $dir ){
			$result = $this->getTreeItems( $dir );
			return $result;
		}
		function getTreeItems( $dir, $root=true, $init=true ){									
			$result = '';			
			if( $init ){
				$this->treedir = $dir;
				if( $root ){
					$result = '<ul><li id="/"><div class="tree-row"><div class="tree-image"></div><span class="root open"><a href="javascript:;">'. JText::_('Root') .'</a></span></div>';
					$dir = '/';
				}
			}
			$folders = $this->getFolders( $dir );
			if( $folders ){
				$result .= '<ul>'; 
				foreach( $folders as $folder ) {
					$result .= '<li id="'. $this->jsEscape( $folder['url'] ) .'"><div class="tree-row"><div class="tree-image"></div><span class="folder"><a href="javascript:;">'. $folder['name'] .'</a></span></div>';
					if( strpos( $this->treedir, $folder['url'] ) !== false ){
						if( $h = $this->getTreeItems( $folder['url'], false ) ){
							$result .= $h;
						}
					}
					$result .= '</li>';
				}
				$result .= '</ul>';
			}
			if( $init && $root ){
				$result .= '</li></ul>';
			}
			$init = false;
			return $result;
		}
		function getFolderDetails( $dir ){
			jimport('joomla.filesystem.folder');
			clearstatcache();
			
			$path 	= Utils::makePath( $this->getBaseDir(), rawurldecode( $dir ) );			
			$date 	= Utils::formatDate( @filemtime( $path ) );
			
			$folders 	= count( JFolder::folders( $path ) );
			$files 		= count( JFolder::files( $path, '\.(' . str_replace( ',', '|', $this->getFileTypes() ) . ')$' ) );
			
			$h = array(
				'modified'	=>	$date,
				'contents'	=>	$folders. ' ' .JText::_('folders'). ', ' .$files. ' ' .JText::_('files')
			);
			return $h;
		}
		function getStdActions(){			
			$this->addAction( 'help', '', '', JText::_('Help') );
			if( $this->checkAccess('upload', '1') ){
				$this->addAction( 'upload', '', '', JText::_('Upload') );
				$this->setXHR( array( $this, 'upload' ) );
			}
			if( $this->checkAccess('folder_new', '1') ){
				$this->addAction( 'folder_new', '', '', JText::_('New Folder') );
				$this->setXHR( array( $this, 'folderNew' ) );
			}
		}
		function addAction( $name, $icon, $action, $title ){
			$this->_actions[$name] = array(
				'name'		=>  $name,
				'icon'		=>	$icon,
				'action'	=>	$action,
				'title'		=>	$title
			);
		}
		function getActions(){
			return $this->json_encode( $this->_actions );
		}
		function removeAction( $name ){
			if( array_key_exists( $this->_actions[$name] ) ){
				unset( $this->_actions[$name] );
			}
		}
		function getStdButtons(){
			if( $this->checkAccess('folder_delete', '1') ){
				$this->addButton( 'folder', 'delete', '', '', JText::_('Delete Folder') );
				
				$this->setXHR( array( $this, 'folderDelete' ) );
			}
			if( $this->checkAccess('folder_rename', '1') ){
				$this->addButton( 'folder', 'rename', '', '', JText::_('Rename Folder') );
				
				$this->setXHR( array( $this, 'folderRename' ) );
			}
			if( $this->checkAccess('file_rename', '1') ){
				$this->addButton( 'file', 'rename', '', '', JText::_('Rename File') );
				
				$this->setXHR( array( $this, 'fileRename' ) );
			}
			if( $this->checkAccess('file_delete', '1') ){
				$this->addButton( 'file', 'delete', '', '', JText::_('Delete Files'), true );
				
				$this->setXHR( array( $this, 'fileDelete' ) );
			}
			if( $this->checkAccess('file_move', '1') ){
				$this->addButton( 'file', 'copy', '', '', JText::_('Copy Files'), true );
				$this->addButton( 'file', 'cut', '', '', JText::_('Cut Files'), true );
				
				$this->addButton( 'file', 'paste', '', '', JText::_('Paste Files'), true, true );
				
				$this->setXHR( array( $this, 'fileCopy' ) );
				$this->setXHR( array( $this, 'fileMove' ) );
			}
			$this->addButton( 'file', 'view', '', '', JText::_('View File') );
			$this->addButton( 'file', 'insert', '', '', JText::_('Insert File') );
		}
		function addButton( $type='file', $name, $icon='', $action='', $title, $multiple=false, $trigger=false ){
			$this->_buttons[$type][$name] = array(
				'name'		=>	$name,
				'icon'		=>	$icon,
				'action'	=>	$action,
				'title'		=>	$title,
				'multiple'	=> 	$multiple,
				'trigger'	=>	$trigger
			);
		}
		function getButtons(){
			return $this->json_encode( $this->_buttons );
		}
		function removeButton( $type, $name ){
			if( array_key_exists( $name, $this->_buttons[$type] ) ){
				unset( $this->_buttons[$type][$name] );
			}
		}
		function changeButton( $type, $name, $keys ){			
			foreach( $keys as $key => $value ){
				if( isset( $this->_buttons[$type][$name][$key] ) ){
					$this->_buttons[$type][$name][$key] = $value;
				}
			}
		}
		function addEvent( $name, $function ){
			$this->_events[$name] = $function;
		}
		function fireEvent( $name, $args=null ){
			if( array_key_exists( $name, $this->_events ) ){
				return call_user_func_array( array( $this, $this->_events[$name] ), $args );
			}
			return $this->_result;
		}
		function getItems( $relative, $args ){			
			$files 		= Manager::getFiles( $relative, '\.(?i)(' . str_replace( ',', '|', $this->getFileTypes( 'list' ) ) . ')$' );
			$folders 	= Manager::getFolders( $relative );
			
			$folderArray 	= array();
			$fileArray 		= array();
			if( $folders ){
				foreach( $folders as $folder ){
					$classes 	= array();
					$classes[] 	= is_writable( Utils::makePath( $this->getBaseDir(), $folder['url'] ) ) ? 'writable' : 'notwritable';
					$classes[] 	= preg_match( '/[^a-z0-9\.\_\-]/i', $folder['name'] ) ? 'notsafe' : 'safe';
					
					$folderArray[] = array(
						'name'		=>	$folder['name'],
						'id'		=>	$folder['url'],
						'classes'	=>	implode( ' ', $classes )
					);
				}
			}
			if( $files ){
				foreach( $files as $file ){
					$classes 	= array();
					$classes[] 	= is_writable( Utils::makePath( $this->getBaseDir(), $file['url'] ) ) ? 'writable' : 'notwritable';
					$classes[] 	= preg_match( '/[^a-z0-9\.\_\-]/i', $file['name'] ) ? 'notsafe' : 'safe';
					
					$fileArray[] = array(
						'name'		=>	$file['name'],
						'id'		=>	$file['url'],
						'classes'	=>	implode( ' ', $classes )
					);
				}
			}
			$result = array( 
				'folders'	=>	$folderArray,
				'files'		=>	$fileArray
			);
			// Fire Event passing result by reference
			$this->fireEvent( 'onGetItems', array( &$result, $args ) );
			return $result;
		}
		function getFileIcon( $ext ){
			if( JFile::exists( JCE_LIBRARIES . '/img/icons/' . $ext . '.gif' )){
				return $this->image( 'libraries.icons/' . $ext . '.gif' );
			}elseif( JFile::exists( $this->getPluginPath() . '/img/icons/' . $ext . '.gif' )){
				return $this->image( 'plugins.icons/' . $ext . '.gif' );
			}else{
				return $this->image( 'libraries.icons/def.gif' );
			}
		}
		function loadBrowser(){
			$session =& JFactory::getSession();
			
			jimport('joomla.application.component.view');
			$browser = new JView( $config = array(
				'base_path' 	=> JCE_LIBRARIES,
				'layout' 		=> 'browser'
			) );
			$browser->assignRef( 'session', $session );
			$browser->assign( 'action', $this->getFormAction() );
			$browser->display();
		}
		function upload(){
			$file = JRequest::getVar( 'Filedata', '', 'files', 'array' );
			
			$dir		= JRequest::getVar( 'upload-dir', '' );
			$overwrite 	= JRequest::getVar( 'upload-overwrite', '0' );	
			$name		= JRequest::getVar( 'upload-name' );
			$method		= JRequest::getVar( 'upload-method', 'swf' );

			$this->_result = array(
				'error' 	=> true,
				'result'	=> ''
			);
			
			if( isset( $file['name'] ) ){
				jimport('joomla.filesystem.file' );
				
				$max_size 	= intval( $this->getSharedParam( 'max_size', '1024' ) )*1024;
				
				$allowable 	= $this->getFileTypes( 'array' );
				$extension  = strtolower( JFile::getExt( $file['name'] ) );
				
				if( !$name ){
					$name = JFile::stripExt( $file['name'] );
				}	
				$path = Utils::makePath( $this->getBaseDir(), rawurldecode( $dir ) );
				$dest = Utils::makePath( $path, Utils::makeSafe( $name . '.' . $extension ) );
				
				// Begin conditions			
				if( $file['size'] > $max_size ){
					if( $method == 'html' ){
						$this->_result['text'] = JText::_('Upload Size Error');
					}else{
						header('HTTP/1.0 400 File size exceeds maximum size');
						echo JText::_('Upload Size Error');
					}
				}elseif( !in_array( $extension, $allowable ) ){
					if( $method == 'html' ){
						$this->_result['text'] = JText::_('Upload Extension Error');
					}else{
						header('HTTP/1.0 415 Unsupported Media Type');
						echo JText::_('Upload Extension Error');
					}
				}else{									
					if( $overwrite == 1 ){
						while( JFile::exists( $dest ) ){
							$name .= '_copy';
							$dest = Utils::makePath( $path, Utils::makeSafe( $name . '.' . $extension ) );
						}
					}
					
					if( !JFile::upload( $file['tmp_name'], $dest ) ){
						if( $method == 'html' ){
							$this->_result['text'] = JText::_('Upload Error');
						}else{
							header('HTTP/1.0 400 Bad Request');
							echo JText::_('Upload Error');
						}
					}else{
						if( !JFile::exists( $dest ) ){
							if( $method == 'html' ){
								$this->_result['text'] = JText::_('Upload Error');
							}else{
								header('HTTP/1.0 400 Bad Request');
								echo JText::_('Upload Error');
							}
						}else{
							if( $method == 'html' ){
								$this->_result['text'] 	= basename( $dest );
								$this->_result['error'] = false;
							}
							$this->_result = $this->fireEvent('onUpload', array( $dest ) );
						}
					}
				}
			}
			return $this->returnResult();
        }
        /**
         * Delete the relative file(s).
         * @param $files the relative path to the file name or comma seperated list of multiple paths.
         * @return string $error on failure.
         */
        function fileDelete( $files ){
			jimport('joomla.filesystem.file');
			$files = explode( ",", rawurldecode( $files ) );
            foreach( $files as $file ){
                $path = Utils::makePath( $this->getBaseDir(), rawurldecode( $file ) );
                if( JFile::exists( $path ) ){
                    if( @!JFile::delete( $path ) ){
						$this->_result['error'] = JText::_('Delete File Error');
					}else{
						$this->_result = $this->fireEvent('onFileDelete', array( rawurldecode( $file ) ) );
					}
				}
            }
			return $this->returnResult();
        }
        /**
	     * Delete a folder
         * @param string $relative The relative path of the folder to delete
	     * @return string $error on failure
	     */
        function folderDelete( $relative ){
            jimport('joomla.filesystem.folder');
			$folder = Utils::makePath( $this->getBaseDir(), rawurldecode( $relative ) );
            if( Utils::countFiles( $folder, '^[(index.html)]' ) != 0 || Utils::countDirs( $folder ) != 0 ){
                $this->_result['error'] = JText::_('Folder Not Empty');
            }else{
                if( !@JFolder::delete( $folder ) ){
                    $this->_result['error'] = JText::_('Delete Folder Error');
                }else{
					$this->_result = $this->fireEvent('onFolderDelete');
				}
            }
            return $this->returnResult();
        }
        /*
        * Rename a file.
        * @param string $src The relative path of the source file
        * @param string $dest The name of the new file
        * @return string $error
        */
        function fileRename( $src, $dest ){			            
			jimport('joomla.filesystem.file');
			$src = Utils::makePath( $this->getBaseDir(), rawurldecode( $src ) );

            $dir = dirname( $src );
            $ext = JFile::getExt( $src );

            $dest = Utils::makePath( $dir, $dest.'.'.$ext );
            if( !JFile::move( $src, $dest ) ){
				$this->_result['error'] = JText::_('Rename File Error');
			}else{
				$this->_result = $this->fireEvent('onFileRename');
			}
            return $this->returnResult();
        }
        /*
        * Rename a folder.
        * @param string $src The relative path of the source file
        * @param string $dest The name of the new folder
        * @return array $error
        */
        function folderRename( $src, $dest ){
            jimport('joomla.filesystem.folder');
			$src = Utils::makePath( $this->getBaseDir(), rawurldecode( $src ) );

            $dir = dirname( $src );

            $dest = Utils::makePath( $dir, $dest );
            if( !JFolder::move( $src, $dest ) ){
				$this->_result['error'] = JText::_('Rename Folder Error');
			}else{
				$this->_result = $this->fireEvent('onFolderRename');
			}
			return $this->returnResult(); 
        }
        /*
        * Copy a file.
        * @param string $files The relative file or comma seperated list of files
        * @param string $dest The relative path of the destination dir
        * @return string $error on failure
        */
        function fileCopy( $files, $dest ){			
            jimport('joomla.filesystem.file');
			$files = explode( ",", rawurldecode( $files ) );
            foreach( $files as $file ){
                $filesrc 	= Utils::makePath( $this->getBaseDir(), $file );
                $filedest 	= Utils::makePath( $this->getBaseDir(), Utils::makePath( $dest, basename( $file ) ) );
				
				if( !JFile::copy( $filesrc, $filedest ) ){
					$this->_result['error'] = JText::_('Copy File Error');
				}else{
					$this->_result = $this->fireEvent('onFileCopy');
				}
            }
            return $this->returnResult();
        }
        /*
        * Copy a file.
        * @param string $files The relative file or comma seperated list of files
        * @param string $dest The relative path of the destination dir
        * @return string $error on failure
        */
        function fileMove( $files, $dest ){			
			jimport('joomla.filesystem.file');
			$files = explode( ",", rawurldecode( $files ) );
            foreach( $files as $file ){
                $filesrc 	= Utils::makePath( $this->getBaseDir(), $file );
                $filedest 	= Utils::makePath( $this->getBaseDir(), Utils::makePath( $dest, basename( $file ) ) );
				
                if( !JFile::move( $filesrc, $filedest ) ){
					$this->_result['error'] = JText::_('Move File Error');
				}else{
					$this->_result = $this->fireEvent('onFileMove');
				}
            }
            return $this->returnResult();
        }
		/**
		* New folder base function. A wrapper for the JFolder::create function
		* @param string $folder The folder to create
		* @return boolean true on success
		*/
		function folderCreate( $folder ){
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');
			
			if( @JFolder::create( $folder ) ){
				$html = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				$file = $folder .DS. "index.html";
				@JFile::write( $file, $html );
			}else{
				return false;
			}
			return true;
		}
		/**
		* New folder
		* @param string $dir The base dir
		* @param string $new_dir The folder to be created
		* @return string $error on failure
		*/
		function folderNew( $dir, $new ){						
			$dir = Utils::makePath( rawurldecode( $dir ), Utils::makeSafe( $new ) );
			$dir = Utils::makePath( $this->getBaseDir(), $dir );
			if( !Manager::folderCreate( $dir ) ){
				$this->_result['error'] = JText::_('New Folder Error');
			}else{
				$this->_result = $this->fireEvent('onFolderNew');
			}
			return $this->returnResult();
		}
}
?>
