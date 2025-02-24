<?php
/**
 * ReplyTest class file.
 *
 * @package HCaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace HCaptcha\Tests\Integration\BBPress;

use HCaptcha\BBPress\Reply;
use HCaptcha\Tests\Integration\HCaptchaPluginWPTestCase;
use WP_Error;

/**
 * Test Reply class.
 */
class ReplyTest extends HCaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'bbpress/bbpress.php';

	/**
	 * Tear down test.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		unset( $_POST );
		bbpress()->errors = new WP_Error();

		parent::tearDown();
	}

	/**
	 * Test add_captcha().
	 */
	public function test_add_captcha() {
		$nonce    = wp_nonce_field( 'hcaptcha_bbp_reply', 'hcaptcha_bbp_reply_nonce', true, false );
		$expected = $this->get_hcap_form() . $nonce;
		$subject  = new Reply();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify() {
		$this->prepare_hcaptcha_get_verify_message( 'hcaptcha_bbp_reply_nonce', 'hcaptcha_bbp_reply' );

		$expected = new WP_Error();
		$subject  = new Reply();

		self::assertTrue( $subject->verify() );

		self::assertEquals( $expected, bbpress()->errors );
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify_not_verified() {
		$expected = new WP_Error( 'hcap_error', 'Please complete the hCaptcha.' );
		$subject  = new Reply();

		self::assertFalse( $subject->verify() );

		self::assertEquals( $expected, bbpress()->errors );
	}
}
