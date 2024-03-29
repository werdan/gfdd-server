<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/MockObject/Generator.php';
require_once 'PHPUnit/Framework/MockObject/Matcher/InvokedAtLeastOnce.php';
require_once 'PHPUnit/Framework/MockObject/Matcher/InvokedAtIndex.php';
require_once 'PHPUnit/Framework/MockObject/Matcher/InvokedCount.php';
require_once 'PHPUnit/Framework/MockObject/Stub.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Util/GlobalState.php';
require_once 'PHPUnit/Util/InvalidArgumentHelper.php';
require_once 'PHPUnit/Util/PHP.php';
require_once 'PHPUnit/Util/Template.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestCase defines the fixture to run multiple tests.
 *
 * To define a TestCase
 *
 *   1) Implement a subclass of PHPUnit_Framework_TestCase.
 *   2) Define instance variables that store the state of the fixture.
 *   3) Initialize the fixture state by overriding setUp().
 *   4) Clean-up after a test by overriding tearDown().
 *
 * Each test runs in its own fixture so there can be no side effects
 * among test runs.
 *
 * Here is an example:
 *
 * <code>
 * <?php
 * require_once 'PHPUnit/Framework/TestCase.php';
 *
 * class MathTest extends PHPUnit_Framework_TestCase
 * {
 *     public $value1;
 *     public $value2;
 *
 *     protected function setUp()
 *     {
 *         $this->value1 = 2;
 *         $this->value2 = 3;
 *     }
 * }
 * ?>
 * </code>
 *
 * For each test implement a method which interacts with the fixture.
 * Verify the expected results with assertions specified by calling
 * assert with a boolean.
 *
 * <code>
 * <?php
 * public function testPass()
 * {
 *     $this->assertTrue($this->value1 + $this->value2 == 5);
 * }
 * ?>
 * </code>
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 3.4.13
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
$classAlreadyLoaded = false;

foreach (get_declared_classes () as $class) {
	if ("PHPUnit_Framework_TestCase" == $class) {
		$classAlreadyLoaded = true;
	}
}

if (!$classAlreadyLoaded) {
	
abstract class PHPUnit_Framework_TestCase extends PHPUnit_Framework_Assert implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * Enable or disable the backup and restoration of the $GLOBALS array.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var    boolean
     */
    protected $backupGlobals = NULL;

    /**
     * @var    array
     */
    protected $backupGlobalsBlacklist = array();

    /**
     * Enable or disable the backup and restoration of static attributes.
     * Overwrite this attribute in a child class of TestCase.
     * Setting this attribute in setUp() has no effect!
     *
     * @var    boolean
     */
    protected $backupStaticAttributes = NULL;

    /**
     * @var    array
     */
    protected $backupStaticAttributesBlacklist = array();

    /**
     * Whether or not this test is to be run in a separate PHP process.
     *
     * @var    boolean
     */
    protected $runTestInSeparateProcess = NULL;

    /**
     * Whether or not this test should preserve the global state when running in a separate PHP process.
     *
     * @var    boolean
     */
    protected $preserveGlobalState = TRUE;

    /**
     * Whether or not this test is running in a separate PHP process.
     *
     * @var    boolean
     */
    protected $inIsolation = FALSE;

    /**
     * @var    array
     */
    protected $data = array();

    /**
     * @var    string
     */
    protected $dataName = '';

    /**
     * @var    boolean
     */
    protected $useErrorHandler = NULL;

    /**
     * @var    boolean
     */
    protected $useOutputBuffering = NULL;

    /**
     * The name of the expected Exception.
     *
     * @var    mixed
     */
    protected $expectedException = NULL;

    /**
     * The message of the expected Exception.
     *
     * @var    string
     */
    protected $expectedExceptionMessage = '';

    /**
     * The code of the expected Exception.
     *
     * @var    integer
     */
    protected $expectedExceptionCode;

    /**
     * Fixture that is shared between the tests of a test suite.
     *
     * @var    mixed
     */
    protected $sharedFixture;

    /**
     * The name of the test case.
     *
     * @var    string
     */
    protected $name = NULL;

    /**
     * @var    array
     */
    protected $dependencies = array();

    /**
     * @var    array
     */
    protected $dependencyInput = array();

    /**
     * @var    string
     */
    protected $exceptionMessage = NULL;

    /**
     * @var    integer
     */
    protected $exceptionCode = 0;

    /**
     * @var    Array
     */
    protected $iniSettings = array();

    /**
     * @var    Array
     */
    protected $locale = array();

    /**
     * @var    Array
     */
    protected $mockObjects = array();

    /**
     * @var    integer
     */
    protected $status;

    /**
     * @var    string
     */
    protected $statusMessage = '';

    /**
     * @var    integer
     */
    protected $numAssertions = 0;

    /**
     * @var PHPUnit_Framework_TestResult
     */
    protected $result;

    /**
     * @var mixed
     */
    protected $testResult;

    /**
     * Constructs a test case with the given name.
     *
     * @param  string $name
     * @param  array  $data
     * @param  string $dataName
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        if ($name !== NULL) {
            $this->setName($name);
        }

        $this->data     = $data;
        $this->dataName = $dataName;
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     */
    public function toString()
    {
        $class = new ReflectionClass($this);

        $buffer = sprintf(
          '%s::%s',

          $class->name,
          $this->getName(FALSE)
        );

        return $buffer . $this->getDataSetAsString();
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return integer
     */
    public function count()
    {
        return 1;
    }

    /**
     * Returns the annotations for this test.
     *
     * @return array
     * @since Method available since Release 3.4.0
     */
    public function getAnnotations()
    {
        return PHPUnit_Util_Test::parseTestMethodAnnotations(
          get_class($this), $this->name
        );
    }

    /**
     * Gets the name of a TestCase.
     *
     * @param  boolean $withDataSet
     * @return string
     */
    public function getName($withDataSet = TRUE)
    {
        if ($withDataSet) {
            return $this->name . $this->getDataSetAsString(FALSE);
        } else {
            return $this->name;
        }
    }

    /**
     * @return string
     * @since  Method available since Release 3.2.0
     */
    public function getExpectedException()
    {
        return $this->expectedException;
    }

    /**
     * @param  mixed   $exceptionName
     * @param  string  $exceptionMessage
     * @param  integer $exceptionCode
     * @since  Method available since Release 3.2.0
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = 0)
    {
        $this->expectedException        = $exceptionName;
        $this->expectedExceptionMessage = $exceptionMessage;
        $this->expectedExceptionCode    = $exceptionCode;
    }

    /**
     * @since  Method available since Release 3.4.0
     */
    protected function setExpectedExceptionFromAnnotation()
    {
        try {
            $method            = new ReflectionMethod(get_class($this), $this->name);
            $methodDocComment  = $method->getDocComment();
            $expectedException = PHPUnit_Util_Test::getExpectedException($methodDocComment);

            if ($expectedException !== FALSE) {
                $this->setExpectedException(
                  $expectedException['class'],
                  $expectedException['message'],
                  $expectedException['code']
                );
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * @param boolean $useErrorHandler
     * @since Method available since Release 3.4.0
     */
    public function setUseErrorHandler($useErrorHandler)
    {
        $this->useErrorHandler = $useErrorHandler;
    }

    /**
     * @since Method available since Release 3.4.0
     */
    protected function setUseErrorHandlerFromAnnotation()
    {
        try {
            $useErrorHandler = PHPUnit_Util_Test::getErrorHandlerSettings(
              get_class($this), $this->name
            );

            if ($useErrorHandler !== NULL) {
                $this->setUseErrorHandler($useErrorHandler);
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * @param boolean $useOutputBuffering
     * @since Method available since Release 3.4.0
     */
    public function setUseOutputBuffering($useOutputBuffering)
    {
        $this->useOutputBuffering = $useOutputBuffering;
    }

    /**
     * @since Method available since Release 3.4.0
     */
    protected function setUseOutputBufferingFromAnnotation()
    {
        try {
            $useOutputBuffering = PHPUnit_Util_Test::getOutputBufferingSettings(
              get_class($this), $this->name
            );

            if ($useOutputBuffering !== NULL) {
                $this->setUseOutputBuffering($useOutputBuffering);
            }
        }

        catch (ReflectionException $e) {
        }
    }

    /**
     * Returns the status of this test.
     *
     * @return integer
     * @since  Method available since Release 3.1.0
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the status message of this test.
     *
     * @return string
     * @since  Method available since Release 3.3.0
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * Returns whether or not this test has failed.
     *
     * @return boolean
     * @since  Method available since Release 3.0.0
     */
    public function hasFailed()
    {
        $status = $this->getStatus();

        return $status == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE ||
               $status == PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
    }

    /**
     * Runs the test case and collects the results in a TestResult object.
     * If no TestResult object is passed a new one will be created.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @return PHPUnit_Framework_TestResult
     * @throws InvalidArgumentException
     */
    public function run(PHPUnit_Framework_TestResult $result = NULL)
    {
        if ($result === NULL) {
            $result = $this->createResult();
        }

        $this->setExpectedExceptionFromAnnotation();
        $this->setUseErrorHandlerFromAnnotation();
        $this->setUseOutputBufferingFromAnnotation();

        if ($this->useErrorHandler !== NULL) {
            $oldErrorHandlerSetting = $result->getConvertErrorsToExceptions();
            $result->convertErrorsToExceptions($this->useErrorHandler);
        }

        $this->result = $result;

        if (!empty($this->dependencies) && !$this->inIsolation) {
            $className  = get_class($this);
            $passed     = $this->result->passed();
            $passedKeys = array_keys($passed);
            $numKeys    = count($passedKeys);

            for ($i = 0; $i < $numKeys; $i++) {
                $pos = strpos($passedKeys[$i], ' with data set');

                if ($pos !== FALSE) {
                    $passedKeys[$i] = substr($passedKeys[$i], 0, $pos);
                }
            }

            $passedKeys = array_flip(array_unique($passedKeys));

            foreach ($this->dependencies as $dependency) {
                if (strpos($dependency, '::') === FALSE) {
                    $dependency = $className . '::' . $dependency;
                }

                if (!isset($passedKeys[$dependency])) {
                    $result->addError(
                      $this,
                      new PHPUnit_Framework_SkippedTestError(
                        sprintf(
                          'This test depends on "%s" to pass.', $dependency
                        )
                      ),
                      0
                    );

                    return;
                } else {
                    if (isset($passed[$dependency])) {
                        $this->dependencyInput[] = $passed[$dependency];
                    } else {
                        $this->dependencyInput[] = NULL;
                    }
                }
            }
        }

        if ($this->runTestInSeparateProcess === TRUE &&
            $this->inIsolation !== TRUE &&
            !$this instanceof PHPUnit_Extensions_SeleniumTestCase &&
            !$this instanceof PHPUnit_Extensions_PhptTestCase) {
            $class                          = new ReflectionClass($this);
            $collectCodeCoverageInformation = $result->getCollectCodeCoverageInformation();

            $template = new PHPUnit_Util_Template(
              sprintf(
                '%s%sProcess%sTestCaseMethod.tpl',

                dirname(__FILE__),
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR
              )
            );

            $template->setVar(
              array(
                'filename'                       => $class->getFileName(),
                'className'                      => $class->getName(),
                'methodName'                     => $this->name,
                'data'                           => addcslashes(serialize($this->data), "'"),
                'dependencyInput'                => addcslashes(serialize($this->dependencyInput), "'"),
                'dataName'                       => $this->dataName,
                'collectCodeCoverageInformation' => $collectCodeCoverageInformation ? 'TRUE' : 'FALSE',
                'included_files'                 => $this->preserveGlobalState ? PHPUnit_Util_GlobalState::getIncludedFilesAsString() : '',
                'constants'                      => $this->preserveGlobalState ? PHPUnit_Util_GlobalState::getConstantsAsString() : '',
                'globals'                        => $this->preserveGlobalState ? PHPUnit_Util_GlobalState::getGlobalsAsString() : '',
                'include_path'                   => addslashes(get_include_path())
              )
            );

            $this->prepareTemplate($template);
            $job = $template->render();
            $result->startTest($this);

            $jobResult = PHPUnit_Util_PHP::runJob($job);

            if (!empty($jobResult['stderr'])) {
                $time = 0;
                $result->addError(
                  $this,
                  new RuntimeException(trim($jobResult['stderr'])), $time
                );
            } else {
                $childResult = @unserialize($jobResult['stdout']);

                if ($childResult !== FALSE) {
                    if (!empty($childResult['output'])) {
                        print $childResult['output'];
                    }

                    $this->testResult    = $childResult['testResult'];
                    $this->numAssertions = $childResult['numAssertions'];
                    $childResult         = $childResult['result'];

                    if ($collectCodeCoverageInformation) {
                        $codeCoverageInformation = $childResult->getRawCodeCoverageInformation();

                        $result->appendCodeCoverageInformation(
                          $this, $codeCoverageInformation[0]['data']
                        );
                    }

                    $time           = $childResult->time();
                    $notImplemented = $childResult->notImplemented();
                    $skipped        = $childResult->skipped();
                    $errors         = $childResult->errors();
                    $failures       = $childResult->failures();

                    if (!empty($notImplemented)) {
                        $result->addError(
                          $this, $notImplemented[0]->thrownException(), $time
                        );
                    }

                    else if (!empty($skipped)) {
                        $result->addError(
                          $this, $skipped[0]->thrownException(), $time
                        );
                    }

                    else if (!empty($errors)) {
                        $result->addError(
                          $this, $errors[0]->thrownException(), $time
                        );
                    }

                    else if (!empty($failures)) {
                        $result->addFailure(
                          $this, $failures[0]->thrownException(), $time
                        );
                    }
                } else {
                    $time = 0;
                    $result->addError(
                      $this,
                      new RuntimeException(trim($jobResult['stdout'])), $time
                    );
                }
            }

            $result->endTest($this, $time);
        } else {
            $result->run($this);
        }

        if ($this->useErrorHandler !== NULL) {
            $result->convertErrorsToExceptions($oldErrorHandlerSetting);
        }

        $this->result = NULL;

        return $result;
    }

    /**
     * Runs the bare test sequence.
     */
    public function runBare()
    {
        $this->numAssertions = 0;

        // Backup the $GLOBALS array and static attributes.
        if ($this->runTestInSeparateProcess !== TRUE &&
            $this->inIsolation !== TRUE) {
            if ($this->backupGlobals === NULL ||
                $this->backupGlobals === TRUE) {
                PHPUnit_Util_GlobalState::backupGlobals(
                  $this->backupGlobalsBlacklist
                );
            }

            if (version_compare(PHP_VERSION, '5.3', '>') &&
                $this->backupStaticAttributes === TRUE) {
                PHPUnit_Util_GlobalState::backupStaticAttributes(
                  $this->backupStaticAttributesBlacklist
                );
            }
        }

        // Start output buffering.
        if ($this->useOutputBuffering === TRUE) {
            ob_start();
        }

        // Clean up stat cache.
        clearstatcache();

        // Run the test.
        try {
            // Set up the fixture.
            $this->setUp();

            // Assert pre-conditions.
            $this->assertPreConditions();

            $this->testResult = $this->runTest();

            // Assert post-conditions.
            $this->assertPostConditions();

            // Verify Mock Object conditions.
            foreach ($this->mockObjects as $mockObject) {
                $this->numAssertions++;
                $mockObject->__phpunit_verify();
                $mockObject->__phpunit_cleanup();
            }

            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        }

        catch (PHPUnit_Framework_IncompleteTest $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->statusMessage = $e->getMessage();
        }

        catch (PHPUnit_Framework_SkippedTest $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->statusMessage = $e->getMessage();
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
            $this->statusMessage = $e->getMessage();
        }

        catch (Exception $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
            $this->statusMessage = $e->getMessage();
        }

        $this->mockObjects = array();

        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            $this->tearDown();
        }

        catch (Exception $_e) {
            if (!isset($e)) {
                $e = $_e;
            }
        }

        // Stop output buffering.
        if ($this->useOutputBuffering === TRUE) {
            ob_end_clean();
        }

        // Clean up stat cache.
        clearstatcache();

        // Restore the $GLOBALS array and static attributes.
        if ($this->runTestInSeparateProcess !== TRUE &&
            $this->inIsolation !== TRUE) {
            if ($this->backupGlobals === NULL ||
                $this->backupGlobals === TRUE) {
                PHPUnit_Util_GlobalState::restoreGlobals(
                   $this->backupGlobalsBlacklist
                );
            }

            if (version_compare(PHP_VERSION, '5.3', '>') &&
                $this->backupStaticAttributes === TRUE) {
                PHPUnit_Util_GlobalState::restoreStaticAttributes();
            }
        }

        // Clean up INI settings.
        foreach ($this->iniSettings as $varName => $oldValue) {
            ini_set($varName, $oldValue);
        }

        $this->iniSettings = array();

        // Clean up locale settings.
        foreach ($this->locale as $category => $locale) {
            setlocale($category, $locale);
        }

        // Workaround for missing "finally".
        if (isset($e)) {
            $this->onNotSuccessfulTest($e);
        }
    }

    /**
     * Override to run the test and assert its state.
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected function runTest()
    {
        if ($this->name === NULL) {
            throw new PHPUnit_Framework_Exception(
              'PHPUnit_Framework_TestCase::$name must not be NULL.'
            );
        }

        try {
            $class  = new ReflectionClass($this);
            $method = $class->getMethod($this->name);
        }

        catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $testResult = $method->invokeArgs(
              $this, array_merge($this->data, $this->dependencyInput)
            );
        }

        catch (Exception $e) {
            if (!$e instanceof PHPUnit_Framework_IncompleteTest &&
                !$e instanceof PHPUnit_Framework_SkippedTest &&
                is_string($this->expectedException) &&
                $e instanceof $this->expectedException) {
                if (is_string($this->expectedExceptionMessage) &&
                    !empty($this->expectedExceptionMessage)) {
                    $this->assertContains(
                      $this->expectedExceptionMessage,
                      $e->getMessage()
                    );
                }

                if (is_int($this->expectedExceptionCode) &&
                    $this->expectedExceptionCode !== 0) {
                    $this->assertEquals(
                      $this->expectedExceptionCode, $e->getCode()
                    );
                }

                $this->numAssertions++;

                return;
            } else {
                throw $e;
            }
        }

        if ($this->expectedException !== NULL) {
            $this->numAssertions++;
            $this->fail('Expected exception ' . $this->expectedException);
        }

        return $testResult;
    }

    /**
     * Sets the name of a TestCase.
     *
     * @param  string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the dependencies of a TestCase.
     *
     * @param  array $dependencies
     * @since  Method available since Release 3.4.0
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * Sets
     *
     * @param  array $dependencyInput
     * @since  Method available since Release 3.4.0
     */
    public function setDependencyInput(array $dependencyInput)
    {
        $this->dependencyInput = $dependencyInput;
    }

    /**
     * Calling this method in setUp() has no effect!
     *
     * @param  boolean $backupGlobals
     * @since  Method available since Release 3.3.0
     */
    public function setBackupGlobals($backupGlobals)
    {
        if (is_null($this->backupGlobals) && is_bool($backupGlobals)) {
            $this->backupGlobals = $backupGlobals;
        }
    }

    /**
     * Calling this method in setUp() has no effect!
     *
     * @param  boolean $backupStaticAttributes
     * @since  Method available since Release 3.4.0
     */
    public function setBackupStaticAttributes($backupStaticAttributes)
    {
        if (is_null($this->backupStaticAttributes) &&
            is_bool($backupStaticAttributes)) {
            $this->backupStaticAttributes = $backupStaticAttributes;
        }
    }

    /**
     * @param  boolean $runTestInSeparateProcess
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.4.0
     */
    public function setRunTestInSeparateProcess($runTestInSeparateProcess)
    {
        if (is_bool($runTestInSeparateProcess)) {
            if ($this->runTestInSeparateProcess === NULL) {
                $this->runTestInSeparateProcess = $runTestInSeparateProcess;
            }
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param  boolean $preserveGlobalState
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.4.0
     */
    public function setPreserveGlobalState($preserveGlobalState)
    {
        if (is_bool($preserveGlobalState)) {
            $this->preserveGlobalState = $preserveGlobalState;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * @param  boolean $inIsolation
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.4.0
     */
    public function setInIsolation($inIsolation)
    {
        if (is_bool($inIsolation)) {
            $this->inIsolation = $inIsolation;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Sets the shared fixture.
     *
     * @param  mixed $sharedFixture
     * @since  Method available since Release 3.1.0
     */
    public function setSharedFixture($sharedFixture)
    {
        $this->sharedFixture = $sharedFixture;
    }

    /**
     * @return mixed
     * @since  Method available since Release 3.4.0
     */
    public function getResult()
    {
        return $this->testResult;
    }

    /**
     * @param  mixed $result
     * @since  Method available since Release 3.4.0
     */
    public function setResult($result)
    {
        $this->testResult = $result;
    }

    /**
     * This method is a wrapper for the ini_set() function that automatically
     * resets the modified php.ini setting to its original value after the
     * test is run.
     *
     * @param  string  $varName
     * @param  string  $newValue
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @since  Method available since Release 3.0.0
     */
    protected function iniSet($varName, $newValue)
    {
        if (!is_string($varName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $currentValue = ini_set($varName, $newValue);

        if ($currentValue !== FALSE) {
            $this->iniSettings[$varName] = $currentValue;
        } else {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'INI setting "%s" could not be set to "%s".',
                $varName,
                $newValue
              )
            );
        }
    }

    /**
     * This method is a wrapper for the setlocale() function that automatically
     * resets the locale to its original value after the test is run.
     *
     * @param  integer $category
     * @param  string  $locale
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @since  Method available since Release 3.1.0
     */
    protected function setLocale()
    {
        $args = func_get_args();

        if (count($args) < 2) {
            throw new InvalidArgumentException;
        }

        $category = $args[0];
        $locale   = $args[1];

        $categories = array(
          LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME
        );

        if (defined('LC_MESSAGES')) {
            $categories[] = LC_MESSAGES;
        }

        if (!in_array($category, $categories)) {
            throw new InvalidArgumentException;
        }

        if (!is_array($locale) && !is_string($locale)) {
            throw new InvalidArgumentException;
        }

        $this->locale[$category] = setlocale($category, NULL);

        $result = call_user_func_array( 'setlocale', $args );

        if ($result === FALSE) {
            throw new PHPUnit_Framework_Exception(
              'The locale functionality is not implemented on your platform, ' .
              'the specified locale does not exist or the category name is ' .
              'invalid.'
            );
        }
    }

    /**
     * Returns a mock object for the specified class.
     *
     * @param  string  $originalClassName
     * @param  array   $methods
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.0.0
     */
    protected function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if (!is_string($originalClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($mockClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'string');
        }

        if (!is_array($methods) && !is_null($methods)) {
            throw new InvalidArgumentException;
        }

        if ($mockClassName != '' && class_exists($mockClassName, FALSE)) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Class "%s" already exists.',
                $mockClassName
              )
            );
        }

        $mock = PHPUnit_Framework_MockObject_Generator::generate(
          $originalClassName,
          $methods,
          $mockClassName,
          $callOriginalClone,
          $callAutoload
        );

        if (!class_exists($mock['mockClassName'], FALSE)) {
            eval($mock['code']);
        }

        if ($callOriginalConstructor && !interface_exists($originalClassName, $callAutoload)) {
            if (count($arguments) == 0) {
                $mockObject = new $mock['mockClassName'];
            } else {
                $mockClass  = new ReflectionClass($mock['mockClassName']);
                $mockObject = $mockClass->newInstanceArgs($arguments);
            }
        } else {
            // Use a trick to create a new object of a class
            // without invoking its constructor.
            $mockObject = unserialize(
              sprintf(
                'O:%d:"%s":0:{}',
                strlen($mock['mockClassName']), $mock['mockClassName']
              )
            );
        }

        $this->mockObjects[] = $mockObject;

        return $mockObject;
    }

    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods are not mocked.
     *
     * @param  string  $originalClassName
     * @param  array   $arguments
     * @param  string  $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     * @since  Method available since Release 3.4.0
     * @throws InvalidArgumentException
     */
    protected function getMockForAbstractClass($originalClassName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if (!is_string($originalClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($mockClassName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'string');
        }

        if (class_exists($originalClassName, $callAutoload)) {
            $methods   = array();
            $reflector = new ReflectionClass($originalClassName);

            foreach ($reflector->getMethods() as $method) {
                if ($method->isAbstract()) {
                    $methods[] = $method->getName();
                }
            }

            if (empty($methods)) {
                $methods = NULL;
            }

            return $this->getMock(
              $originalClassName,
              $methods,
              $arguments,
              $mockClassName,
              $callOriginalConstructor,
              $callOriginalClone,
              $callAutoload
            );
        } else {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Class "%s" does not exist.',
                $originalClassName
              )
            );
        }
    }

    /**
     * Returns a mock object based on the given WSDL file.
     *
     * @param  string  $wsdlFile
     * @param  string  $originalClassName
     * @param  string  $mockClassName
     * @param  array   $methods
     * @param  boolean $callOriginalConstructor
     * @return PHPUnit_Framework_MockObject_MockObject
     * @since  Method available since Release 3.4.0
     */
    protected function getMockFromWsdl($wsdlFile, $originalClassName = '', $mockClassName = '', array $methods = array(), $callOriginalConstructor = TRUE)
    {
        if ($originalClassName === '') {
            $originalClassName = str_replace(
              '.wsdl', '', basename($wsdlFile)
            );
        }

        eval(
          PHPUnit_Framework_MockObject_Generator::generateClassFromWsdl(
            $wsdlFile, $originalClassName, $methods
          )
        );

        return $this->getMock(
          $originalClassName,
          $methods,
          array('', array()),
          $mockClassName,
          $callOriginalConstructor,
          FALSE,
          FALSE
        );
    }

    /**
     * Adds a value to the assertion counter.
     *
     * @param integer $count
     * @since Method available since Release 3.3.3
     */
    public function addToAssertionCount($count)
    {
        $this->numAssertions += $count;
    }

    /**
     * Returns the number of assertions performed by this test.
     *
     * @return integer
     * @since  Method available since Release 3.3.0
     */
    public function getNumAssertions()
    {
        return $this->numAssertions;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed zero or more times.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     * @since  Method available since Release 3.0.0
     */
    protected function any()
    {
        return new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is never executed.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     * @since  Method available since Release 3.0.0
     */
    protected function never()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(0);
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed at least once.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce
     * @since  Method available since Release 3.0.0
     */
    protected function atLeastOnce()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce;
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed exactly once.
     *
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     * @since  Method available since Release 3.0.0
     */
    protected function once()
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount(1);
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is executed exactly $count times.
     *
     * @param  integer $count
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
     * @since  Method available since Release 3.0.0
     */
    protected function exactly($count)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedCount($count);
    }

    /**
     * Returns a matcher that matches when the method it is evaluated for
     * is invoked at the given $index.
     *
     * @param  integer $index
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
     * @since  Method available since Release 3.0.0
     */
    protected function at($index)
    {
        return new PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex($index);
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_MockObject_Stub_Return
     * @since  Method available since Release 3.0.0
     */
    protected function returnValue($value)
    {
        return new PHPUnit_Framework_MockObject_Stub_Return($value);
    }

    /**
     *
     *
     * @param  integer $argumentIndex
     * @return PHPUnit_Framework_MockObject_Stub_ReturnArgument
     * @since  Method available since Release 3.3.0
     */
    protected function returnArgument($argumentIndex)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnArgument(
          $argumentIndex
        );
    }

    /**
     *
     *
     * @param  mixed $callback
     * @return PHPUnit_Framework_MockObject_Stub_ReturnCallback
     * @since  Method available since Release 3.3.0
     */
    protected function returnCallback($callback)
    {
        return new PHPUnit_Framework_MockObject_Stub_ReturnCallback($callback);
    }

    /**
     *
     *
     * @param  Exception $exception
     * @return PHPUnit_Framework_MockObject_Stub_Exception
     * @since  Method available since Release 3.1.0
     */
    protected function throwException(Exception $exception)
    {
        return new PHPUnit_Framework_MockObject_Stub_Exception($exception);
    }

    /**
     *
     *
     * @param  mixed $value, ...
     * @return PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls
     * @since  Method available since Release 3.0.0
     */
    protected function onConsecutiveCalls()
    {
        $args = func_get_args();

        return new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($args);
    }

    /**
     * @param  mixed $data
     * @return string
     * @since  Method available since Release 3.2.1
     */
    protected function dataToString($data)
    {
        $result = array();

        foreach ($data as $_data) {
            if (is_array($_data)) {
                $result[] = 'array(' . $this->dataToString($_data) . ')';
            }

            else if (is_object($_data)) {
                $object = new ReflectionObject($_data);

                if ($object->hasMethod('__toString')) {
                    $result[] = (string)$_data;
                } else {
                    $result[] = get_class($_data);
                }
            }

            else if (is_resource($_data)) {
                $result[] = '<resource>';
            }

            else {
                $result[] = var_export($_data, TRUE);
            }
        }

        return join(', ', $result);
    }

    /**
     * Gets the data set description of a TestCase.
     *
     * @param  boolean $includeData
     * @return string
     * @since  Method available since Release 3.3.0
     */
    protected function getDataSetAsString($includeData = TRUE)
    {
        $buffer = '';

        if (!empty($this->data)) {
            if (is_int($this->dataName)) {
                $buffer .= sprintf(' with data set #%d', $this->dataName);
            } else {
                $buffer .= sprintf(' with data set "%s"', $this->dataName);
            }

            if ($includeData) {
                $buffer .= sprintf(' (%s)', $this->dataToString($this->data));
            }
        }

        return $buffer;
    }

    /**
     * Creates a default TestResult object.
     *
     * @return PHPUnit_Framework_TestResult
     */
    protected function createResult()
    {
        return new PHPUnit_Framework_TestResult;
    }

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called before the execution of a test starts
     * and after setUp() is called.
     *
     * @since  Method available since Release 3.2.8
     */
    protected function assertPreConditions()
    {
    }

    /**
     * Performs assertions shared by all tests of a test case.
     *
     * This method is called before the execution of a test ends
     * and before tearDown() is called.
     *
     * @since  Method available since Release 3.2.8
     */
    protected function assertPostConditions()
    {
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function tearDownAfterClass()
    {
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param Exception $e
     * @since Method available since Release 3.4.0
     */
    protected function onNotSuccessfulTest(Exception $e)
    {
        throw $e;
    }

    /**
     * Performs custom preparations on the process isolation template.
     *
     * @param PHPUnit_Util_Template $template
     * @since Method available since Release 3.4.0
     */
    protected function prepareTemplate(PHPUnit_Util_Template $template)
    {
    }
}
}
?>
