<?php
/**
 * DokuWiki Plugin colortag (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  jno <jno@pisem.net>
 *
 * Syntax:     <colortag>color,...,color</colortag>
 *
 * Renders as:
 *   <table class='colortag'> ... </table>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_colortag_colortag extends DokuWiki_Syntax_Plugin {
    public function getType() {
        // return 'FIXME: container|baseonly|formatting|substition|protected|disabled|paragraphs';
        return 'protected';
    }

    public function getPType() {
        // return 'FIXME: normal|block|stack';
        return 'block';
    }

    public function getSort() {
        return 195;
    }


    public function connectTo($mode) {
      $this->Lexer->addEntryPattern('<colortag(?=[^\r\n]*?>.*?</colortag>)',$mode,'plugin_colortag_colortag');
    }

    public function postConnect() {
      $this->Lexer->addExitPattern('</colortag>', 'plugin_colortag_colortag');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler){
        switch ($state) {
          case DOKU_LEXER_ENTER:
            $this->syntax = substr($match, 1);
            return false;

          case DOKU_LEXER_UNMATCHED:
             // will include everything from <colortag ... to ... </colortag >
             // e.g. ... [attr] > [content]
             list($attr, $content) = preg_split('/>/u',$match,2);
         $content = explode(',',$content);
         // $attr reserved for future use

             return array($this->syntax, trim($attr), $content);
        }
        return false;
    }

    public function render($mode,  Doku_Renderer $renderer, $data) {
      if($mode != 'xhtml') return false;
      if (count($data) == 3) {
        list($syntax, $attr, $content) = $data;
        if ($syntax != 'colortag') return false;
    $ColorTag = '<table class="colortag"><tbody><tr>';
    foreach($content as $color) {
      $c = trim($color);
      $f = null;
      $l = null;
      $a = explode(':',$c);
      if( count($a) == 3 ) {
        $c = $a[0];
        $f = $a[1];
        $l = $a[2];
      } elseif( count($a) == 2 ) {
        $c = $a[0];
        $f = $a[1];
      } elseif( count($a) != 1 ) {
        $c = '*invalid*';
      }
      if( !(   preg_match('/^[a-z]+$/',$c)
          or preg_match('/^#[0-9a-f]{6,6}$/i',$c)
          or preg_match('/^#[0-9a-f]{3,3}$/i',$c)
      ) ) {
        $c = 'white;color:red';
        $f = 'X';
      } elseif(!is_null($l)) {
        if(    preg_match('/^[a-z]+$/',$l)
          or preg_match('/^#[0-9a-f]{6,6}$/i',$l)
          or preg_match('/^#[0-9a-f]{3,3}$/i',$l)
        ) { $c .= ';color:'.$l; } else { $c = 'white;color:red'; $f = 'Z'; }
      }
      $f = is_null($f) ? '&nbsp;' : strip_tags($f);
      $ColorTag .= '<td style="background-color:'.$c.'">'.$f.'</td>';
    }
    $ColorTag .= '</tr></tbody></table>';
    $renderer->doc .= $ColorTag;
        return true;
      }
      return false;
    }
}

// vim:ts=4:sw=4:et:
