<?php
/**
 * This is the package.xml generator for Text_Parser
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Parser
 * @author     Sergio Carvalho <sergiosgc@php.net>
 * @copyright  2005-2007 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id: package.php,v 1.37 2007/11/20 20:04:24 farell Exp $
 * @link       http://pear.php.net/package/PEAR_PackageFileManager
 * @since      File available since Release 1.6.0
 */
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$release_version = '0.3.0';
$release_state   = 'beta';
$api_version     = '1.0.0';
$api_state   = 'alpha';
$release_notes   = '* Initial version';

$packagexml = new PEAR_PackageFileManager2();
$packagexml->setOptions(
    array(
      'packagefile' => 'package.xml',
      'exceptions' => array(
          'ChangeLog' => 'doc',
          'NEWS' => 'doc'),
      'filelistgenerator' => 'file',
      'packagedirectory' => dirname(__FILE__),
      'changelogoldtonew' => false,
      'baseinstalldir' => '/',
      'simpleoutput' => true,
      'dirroles' => array('tests' => 'test'),
      'ignore' => array('package.php', '_MTN/'),
      ));
$packagexml->setPackage('Text_Parser_Generator');
$packagexml->setSummary('Compiler compiler that produces Text_Parser subclasses');
$packagexml->setDescription(<<<EOS
Text_Parser_Generator is a compiler-compiler. It analyzes a grammar, defined as a Structures_Grammar instance, and produces code for a parser, based on Text_Parser, capable of parsing the provided grammar.

The Structures_Grammar instance can be created with PHP code, or by using a grammar definition grammar and a suitable parser (check Text_Parser_BNF for a BNF parser).
EOS
);
$packagexml->addMaintainer('lead', 'sergiosgc', 'Sergio Carvalho', 'sergiosgc@php.net');
$packagexml->setNotes($release_notes);
$packagexml->addIgnore(array('package.php', '*.tgz'));
$packagexml->setPackageType('php');
$packagexml->addRelease();
$packagexml->clearDeps();
$packagexml->setChannel('pear.sergiocarvalho.com');
$packagexml->setLicense('PHP License 3.01', 'http://www.php.net/license/3_01.txt');
$packagexml->setReleaseVersion($release_version);
$packagexml->setAPIVersion($api_version);
$packagexml->setReleaseStability($release_state);
$packagexml->setAPIStability($api_state);
$packagexml->setPhpDep('5.1.0');
$packagexml->setPearinstallerDep('1.5.4');
$packagexml->addPackageDepWithChannel('required', 'Structures_Grammar', 'pear.sergiocarvalho.com', '0.1.0');
$packagexml->addPackageDepWithChannel('required', 'Text_Parser', 'pear.sergiocarvalho.com', '0.1.0');
$packagexml->generateContents();
$packagexml->writePackageFile();
?>
