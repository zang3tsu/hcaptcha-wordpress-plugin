<?php
/**
 * CreateGroupTest class file.
 *
 * @package HCaptcha\Tests
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpLanguageLevelInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace HCaptcha\Tests\Integration\BuddyPress;

use HCaptcha\BuddyPress\CreateGroup;
use HCaptcha\Tests\Integration\HCaptchaPluginWPTestCase;
use tad\FunctionMocker\FunctionMocker;

/**
 * Test  CreateGroup.
 *
 * @group bp
 */
class CreateGroupTest extends HCaptchaPluginWPTestCase {

	/**
	 * Plugin relative path.
	 *
	 * @var string
	 */
	protected static $plugin = 'buddypress/bp-loader.php';

	/**
	 * Tear down the test.
	 */
	public function tearDown(): void { // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations.voidFound
		global $bp;

		unset( $bp->signup );

		parent::tearDown();
	}

	/**
	 * Test add_captcha().
	 */
	public function test_hcap_bp_group_form() {
		$nonce    = wp_nonce_field( 'hcaptcha_bp_create_group', 'hcaptcha_bp_create_group_nonce', true, false );
		$expected =
			'<div class="hcap_buddypress_group_form">' .
			$this->get_hcap_form() . $nonce .
			'</div>';
		$subject  = new CreateGroup();

		ob_start();

		$subject->add_captcha();

		self::assertSame( $expected, ob_get_clean() );
	}

	/**
	 * Test verify().
	 */
	public function test_verify() {
		FunctionMocker::replace(
			'bp_is_group_creation_step',
			static function ( $step_slug ) {
				return 'group-details' === $step_slug;
			}
		);

		$subject = new CreateGroup();

		$this->prepare_hcaptcha_get_verify_message( 'hcaptcha_bp_create_group_nonce', 'hcaptcha_bp_create_group' );

		self::assertTrue( $subject->verify( null ) );
	}

	/**
	 * Test verify() when not in step.
	 */
	public function test_verify_not_in_step() {
		FunctionMocker::replace( 'bp_is_group_creation_step', false );

		$subject = new CreateGroup();

		self::assertFalse( $subject->verify( null ) );
	}

	/**
	 * Test verify() when not verified.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	public function test_verify_not_verified() {
		FunctionMocker::replace(
			'bp_is_group_creation_step',
			static function ( $step_slug ) {
				return 'group-details' === $step_slug;
			}
		);

		FunctionMocker::replace(
			'defined',
			static function ( $constant_name ) {
				return 'BP_TESTS_DIR' === $constant_name;
			}
		);

		add_filter(
			'wp_redirect',
			static function ( $location, $status ) {
				return '';
			},
			10,
			2
		);

		FunctionMocker::replace( 'bp_get_groups_root_slug', '' );

		$subject = new CreateGroup();

		self::assertFalse( $subject->verify( null ) );

		$bp = buddypress();

		self::assertSame( 'Please complete the hCaptcha.', $bp->template_message );
		self::assertSame( 'error', $bp->template_message_type );
	}
}
