<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  require('includes/classes/banner_manager.php');

  class osC_Content_Banner_manager extends osC_Template {

/* Public variables */
    var $image_extension;

/* Private variables */

    var $_module = 'banner_manager',
        $_page_title = HEADING_TITLE,
        $_page_contents = 'main.php';

/* Class constructor */

    function osC_Content_Banner_manager() {
      global $osC_MessageStack;

      if ( !isset($_GET['action']) ) {
        $_GET['action'] = '';
      }

      if ( !isset($_GET['page']) || ( isset($_GET['page']) && !is_numeric($_GET['page']) ) ) {
        $_GET['page'] = 1;
      }

      $this->image_extension = osc_dynamic_image_extension();

// check if the graphs directory exists
      if ( !empty($this->image_extension) ) {
        if ( is_dir('images/graphs') ) {
          if ( !is_writeable('images/graphs') ) {
            $osC_MessageStack->add('header', ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
          }
        } else {
          $osC_MessageStack->add('header', ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
        }
      }

      if ( !empty($_GET['action']) ) {
        switch ( $_GET['action'] ) {
          case 'preview':
            $this->_page_contents = 'preview.php';

            break;

          case 'statistics':
            $this->_page_contents = 'statistics.php';

            break;

          case 'save':
            if ( isset($_GET['bID']) && is_numeric($_GET['bID']) ) {
              $this->_page_contents = 'edit.php';
            } else {
              $this->_page_contents = 'new.php';
            }

            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              $data = array('title' => $_POST['title'],
                            'url' => $_POST['url'],
                            'group' => $_POST['group'],
                            'group_new' => $_POST['group_new'],
                            'image' => (isset($_FILES['image']) ? $_FILES['image'] : null),
                            'image_local' => $_POST['image_local'],
                            'image_target' => $_POST['image_target'],
                            'html_text' => $_POST['html_text'],
                            'date_scheduled' => $_POST['date_scheduled'],
                            'date_expires' => $_POST['date_expires'],
                            'status' => (isset($_POST['status']) && ($_POST['status'] == 'on') ? true : false));

              if ( osC_BannerManager_Admin::save((isset($_GET['bID']) && is_numeric($_GET['bID']) ? $_GET['bID'] : null), $data) ) {
                $osC_MessageStack->add_session($this->_module, SUCCESS_DB_ROWS_UPDATED, 'success');
              } else {
                $osC_MessageStack->add_session($this->_module, ERROR_DB_ROWS_NOT_UPDATED, 'error');
              }

              osc_redirect(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page']));
            }

            break;

          case 'delete':
            $this->_page_contents = 'delete.php';

            if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
              if ( osC_BannerManager_Admin::delete($_GET['bID'], (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on') ? true : false)) ) {
                $osC_MessageStack->add_session($this->_module, SUCCESS_DB_ROWS_UPDATED, 'success');
              } else {
                $osC_MessageStack->add_session($this->_module, ERROR_DB_ROWS_NOT_UPDATED, 'error');
              }

              osc_redirect(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page']));
            }

            break;

          case 'batchDelete':
            if ( isset($_POST['batch']) && is_array($_POST['batch']) && !empty($_POST['batch']) ) {
              $this->_page_contents = 'batch_delete.php';

              if ( isset($_POST['subaction']) && ($_POST['subaction'] == 'confirm') ) {
                $error = false;

                foreach ($_POST['batch'] as $id) {
                  if ( !osC_BannerManager_Admin::delete($id, (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on') ? true : false)) ) {
                    $error = true;
                    break;
                  }
                }

                if ( $error === false ) {
                  $osC_MessageStack->add_session($this->_module, SUCCESS_DB_ROWS_UPDATED, 'success');
                } else {
                  $osC_MessageStack->add_session($this->_module, ERROR_DB_ROWS_NOT_UPDATED, 'error');
                }

                osc_redirect(osc_href_link_admin(FILENAME_DEFAULT, $this->_module . '&page=' . $_GET['page']));
              }
            }

            break;
        }
      }
    }
  }
?>
