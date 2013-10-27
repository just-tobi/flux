<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Service_ContentServiceTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @var array
	 */
	private static $BACKTRACE_FIXTURE = array(
		array(
			'class' => 'TYPO3\\CMS\\Backend\\View\\PageLayout\\ExtDirect\\ExtdirectPageCommands',
			'function' => 'moveContentElement',
			'args' => array()
		),
		array(
			'class' => 'TYPO3\\CMS\\Backend\\View\\PageLayout\\ExtDirect\\ExtdirectPageCommands',
			'function' => 'unrecognised',
			'args' => array()
		),
		array(
			'class' => 'Unrecognised',
			'function' => 'void',
			'args' => array(
				'foo',
				'bar'
			)
		)
	);

	/**
	 * @return Tx_Flux_Service_ContentService
	 */
	protected function createInstance() {
		$class = substr(get_class($this), 0, -4);
		$instance = $this->objectManager->get($class);
		return $instance;
	}

	/**
	 * @test
	 */
	public function canDetectParentElementAreaFromRecord() {
		$result = $this->createInstance()->detectParentElementAreaFromRecord(0);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectParentUidFromRecord() {
		$result = $this->createInstance()->detectParentUidFromRecord(0);
		$this->assertIsInteger($result);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersReturnsEarlyWithUnrecognisedUrl() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#hascutoffpointbutnovalues'
		);
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$result = $this->createInstance()->affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertFalse($result);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersAppliesContentAreaAndParentWithRecognisedUrl() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#areaname:999999'
		);
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$result = $this->createInstance()->affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertTrue($result);
		$this->assertSame('areaname', $record['tx_flux_column']);
		$this->assertSame('999999', $record['tx_flux_parent']);
	}

	/**
	 * @test
	 */
	public function affectByRequestParametersAppliesContentAreaAndParentWithRecognisedUrlRelativeToElement() {
		$parameters = array(
			'returnUrl' => 'some.php?arg=1#areaname:999999:-999998'
		);
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordIsParentAndHasChildren;
		$oldSorting = $record['sorting'];
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$result = $this->createInstance()->affectRecordByRequestParameters($record, $parameters, $tceMain);
		$this->assertTrue($result);
		$this->assertSame('areaname', $record['tx_flux_column']);
		$this->assertSame('999999', $record['tx_flux_parent']);
		$this->assertNotSame($oldSorting, $record['sorting']);
	}

	/**
	 * @test
	 */
	public function affectsRecordByBacktraceWithDropTargetTop() {
		$expectedParent = 123;
		$expectedColumn = 'myarea';
		$backtrace = self::$BACKTRACE_FIXTURE;
		$backtrace[0]['args'] = array(
			0 => 'unused',
			1 => 'void-void-void-void-top-' . $expectedParent . '-' . $expectedColumn
		);
		$row = $this->fireBacktraceDetection($backtrace);
		$this->assertEquals($expectedColumn, $row['tx_flux_column']);
		$this->assertEquals($expectedParent, $row['tx_flux_parent']);
		$this->assertEquals(-42, $row['colPos']);
	}

	/**
	 * @test
	 */
	public function affectsRecordByBacktraceWithDropTargetAfter() {
		$expectedParent = 123;
		$expectedColumn = '';
		$backtrace = self::$BACKTRACE_FIXTURE;
		$backtrace[0]['args'] = array(
			0 => 'unused',
			1 => 'void-void-void-void-after-' . $expectedParent . '-' . $expectedColumn
		);
		$row = $this->fireBacktraceDetection($backtrace);
		$this->assertEquals($expectedColumn, $row['tx_flux_column']);
		$this->assertEquals(0 - $expectedParent, $row['pid']);
	}

	/**
	 * @test
	 */
	public function affectsRecordByBacktraceWithDropTargetNone() {
		$backtrace = self::$BACKTRACE_FIXTURE;
		$row = $this->fireBacktraceDetection($backtrace);
		$this->assertEmpty($row['tx_flux_column']);
		$this->assertEmpty($row['tx_flux_parent']);
	}

	/**
	 * @test
	 */
	public function canLoadRecordsFromDatabase() {
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'loadRecordsFromDatabase', 'uid IN(0)');
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 */
	public function canLoadRecordFromDatabaseByUid() {
		$instance = $this->createInstance();
		$result = $this->callInaccessibleMethod($instance, 'loadRecordFromDatabase', 9999999999999);
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canUpdateRecordInDatabase() {
		$instance = $this->createInstance();
		$row = array('uid' => 0);
		$this->callInaccessibleMethod($instance, 'updateRecordInDatabase', $row);
	}

	/**
	 * @test
	 */
	public function canInitializeBlankRecord() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$row = array('uid' => -1);
		$tceMain = $this->getMock('t3lib_TCEmain');
		$tceMain->substNEWwithIDs = array();
		$mock->initializeRecord($row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeBlankRecordWithLanguage() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$oldRecord = array(
			'sys_language_uid' => 1
		);
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(999999999999)->will($this->returnValue($oldRecord));
		$row = array('uid' => -1, 't3_origuid' => 999999999999, 'sys_language_uid' => 1);
		$tceMain = $this->getMock('t3lib_TCEmain');
		$tceMain->substNEWwithIDs = array();
		$mock->initializeRecord($row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeBlankRecordWithLanguageInOldRecord() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$mock = $this->createMock($methods);
		$oldRecord = array(
			'sys_language_uid' => 1
		);
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(999999999999)->will($this->returnValue($oldRecord));
		$row = array('uid' => -1, 't3_origuid' => 999999999999);
		$tceMain = $this->getMock('t3lib_TCEmain');
		$tceMain->substNEWwithIDs = array();
		$mock->initializeRecord($row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeCopiedRecordWithoutChildren() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$row = array('uid' => -1, 't3_origuid' => 99999999999999);
		$mock = $this->createMock($methods);
		$mock->expects($this->atLeastOnce())->method('loadRecordsFromDatabase');
		$tceMain = $this->getMock('t3lib_TCEmain');
		$tceMain->substNEWwithIDs = array();
		$mock->initializeRecord($row, $tceMain);
	}

	/**
	 * @test
	 */
	public function canInitializeCopiedRecordWithChildren() {
		$methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDatabase');
		$children = array(
			array('uid' => -1),
			array('uid' => -2)
		);
		$mock = $this->createMock($methods);
		$mock->expects($this->atLeastOnce())->method('loadRecordsFromDatabase')->will($this->returnValue($children));
		$mock->expects($this->exactly(2))->method('updateRecordInDatabase');
		$row = array('uid' => -1, 't3_origuid' => 99999999999999);
		$tceMain = $this->getMock('t3lib_TCEmain', array('localize'));
		$tceMain->substNEWwithIDs = array();
		$mock->initializeRecord($row, $tceMain);
	}

	/**
	 * @test
	 */
	public function moveRecordWithPositiveColumnPositionDetectsParentLocationFromBacktrace() {
		$methods = array('affectRecordByBacktrace');
		$row = array();
		$mock = $this->createMock($methods);
		$mock->expects($this->once())->method('affectRecordByBacktrace')->with($row);
		$relativeTo = 1;
		$mock->moveRecord($row, $relativeTo);
	}

	/**
	 * @test
	 */
	public function moveRecordWithNegativeRelativeToValueLoadsRelativeRecordFromDatabaseAndCopiesValuesToRecordAndSetsColumnPositionAndUpdatesRelativeToValue() {
		$methods = array('loadRecordFromDatabase');
		$mock = $this->createMock($methods);
		$row = array(
			'pid' => 1
		);
		$relativeRecord = array(
			'tx_flux_column' => 2,
			'tx_flux_parent' => 2,
			'colPos' => -42
		);
		$relativeTo = -1;
		$mock->expects($this->once())->method('loadRecordFromDatabase')->with(1)->will($this->returnValue($relativeRecord));
		$mock->moveRecord($row, $relativeTo);
		$this->assertEquals($relativeRecord['tx_flux_column'], $row['tx_flux_column']);
		$this->assertEquals($relativeRecord['tx_flux_parent'], $row['tx_flux_parent']);
		$this->assertEquals($relativeRecord['colPos'], $row['colPos']);
		$this->assertEquals(1, $relativeTo);
	}

	/**
	 * @test
	 */
	public function moveRecordWithCombinedFluxRelativeToValueSetsExpectedRecordPropertiesAndUpdatesRelativeToValue() {
		$methods = array('affectRecordByBacktrace');
		$mock = $this->createMock($methods);
		$row = array(
			'pid' => 1
		);
		$relativeTo = 'area-1-2-FLUX';
		$mock->moveRecord($row, $relativeTo);
		$this->assertEquals('area', $row['tx_flux_column']);
		$this->assertEquals('1', $row['tx_flux_parent']);
		$this->assertEquals('2', $row['pid']);
		$this->assertEquals(-1, $row['sorting']);
		$this->assertEquals(2, $relativeTo);
	}

	/**
	 * @test
	 */
	public function moveRecordWithCombinedGridelementsRelativeToValueSetsExpectedRecordPropertiesAndUpdatesRelativeToValue() {
		$methods = array('affectRecordByBacktrace');
		$mock = $this->createMock($methods);
		$row = array(
			'pid' => 1
		);
		$relativeTo = '1x2';
		$mock->moveRecord($row, $relativeTo);
		$this->assertEquals(NULL, $row['tx_flux_column']);
		$this->assertEquals(NULL, $row['tx_flux_parent']);
		$this->assertEquals('2', $row['colPos']);
		$this->assertEquals(-1, $row['sorting']);
		$this->assertEquals(1, $relativeTo);
	}

	/**
	 * @param array $backtrace
	 * @return array
	 */
	protected function fireBacktraceDetection($backtrace) {
		$row = array();
		$this->createInstance()->affectRecordByBacktrace($row, $backtrace);
		return $row;
	}

	/**
	 * @param array $functions
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	protected function createMock($functions = array()) {
		$class = substr(get_class($this), 0, -4);
		$mock = $this->getMock($class, $functions);
		return $mock;
	}

}
