<?php
/**
 * Preventivo calculation tests.
 *
 * @package MediaconEnterprise
 */

use Mediacon\Enterprise\Core\SettingsManager;
use Mediacon\Enterprise\Modules\Preventivo\Domain\DisputeValue;
use Mediacon\Enterprise\Modules\Preventivo\Domain\InterestCenter;
use Mediacon\Enterprise\Modules\Preventivo\Domain\LiveExpense;
use Mediacon\Enterprise\Modules\Preventivo\Domain\MediationType;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Money;
use Mediacon\Enterprise\Modules\Preventivo\Domain\PaidAmount;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Party;
use Mediacon\Enterprise\Modules\Preventivo\Domain\Scenario;
use Mediacon\Enterprise\Modules\Preventivo\Services\PricingConfiguration;
use Mediacon\Enterprise\Modules\Preventivo\Services\QuoteCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Verifies the isolated per-party calculation rules.
 */
final class PreventivoCalculatorTest extends TestCase {

	private QuoteCalculator $calculator;

	protected function setUp(): void {
		$GLOBALS['mediacon_test_options']['mediacon_enterprise_settings'] = array(
			'enabled_modules' => array( 'preventivo' ),
			'preventivo'      => array(
				'brackets'   => array( array( 'max' => 0, 'fee' => 100 ) ),
				'reductions' => array( 'mandatory' => 20, 'court_ordered' => 99, 'voluntary' => 0 ),
				'increases'  => array(
					'absence'              => 0,
					'first_no_agreement'    => 0,
					'first_agreement'       => 10,
					'continuation'          => 5,
					'multiple_no_agreement' => 15,
					'multiple_agreement'    => 25,
					'mediator_proposal'     => 20,
				),
			),
		);
		$this->calculator = new QuoteCalculator( new PricingConfiguration( new SettingsManager() ) );
	}

	public function testMandatoryReduction(): void {
		$summary = $this->calculate( MediationType::MANDATORY, Scenario::FIRST_NO_AGREEMENT, array( $this->party( 'a', Party::PRESENT ) ) );
		self::assertSame( 80.0, $summary->generalTotal()->amount() );
	}

	public function testCourtOrderedUsesMandatoryEconomicRegime(): void {
		$summary = $this->calculate( MediationType::COURT_ORDERED, Scenario::FIRST_NO_AGREEMENT, array( $this->party( 'a', Party::PRESENT ) ) );
		self::assertSame( 80.0, $summary->generalTotal()->amount() );
		self::assertSame( MediationType::MANDATORY, $summary->toArray()['regime'] );
	}

	public function testVoluntaryDoesNotApplyReduction(): void {
		$summary = $this->calculate( MediationType::VOLUNTARY, Scenario::FIRST_NO_AGREEMENT, array( $this->party( 'a', Party::PRESENT ) ) );
		self::assertSame( 100.0, $summary->generalTotal()->amount() );
	}

	public function testMultiplePartiesRemainAutonomous(): void {
		$summary = $this->calculate( MediationType::VOLUNTARY, Scenario::FIRST_NO_AGREEMENT, array( $this->party( 'a', Party::PRESENT ), $this->party( 'b', Party::PRESENT, Party::ROLE_INVITED ) ) );
		self::assertCount( 2, $summary->parties() );
		self::assertSame( 100.0, $summary->parties()[0]->total()->amount() );
		self::assertSame( 100.0, $summary->parties()[1]->total()->amount() );
		self::assertSame( 200.0, $summary->generalTotal()->amount() );
	}

	public function testMultipleInterestCentersAreChargedToTheirPartyOnly(): void {
		$summary = $this->calculate( MediationType::VOLUNTARY, Scenario::FIRST_NO_AGREEMENT, array( $this->party( 'a', Party::PRESENT, Party::ROLE_CLAIMANT, 3 ) ) );
		self::assertSame( 300.0, $summary->generalTotal()->amount() );
	}

	public function testAbsentInvitedPartyIsZeroAndDoesNotDoubleTotal(): void {
		$claimant = $this->party( 'claimant', Party::PRESENT );
		$absent   = $this->party( 'absent', Party::ABSENT, Party::ROLE_INVITED );
		$summary  = $this->calculate( MediationType::VOLUNTARY, Scenario::ABSENCE, array( $claimant, $absent ) );
		self::assertSame( 100.0, $summary->parties()[0]->total()->amount() );
		self::assertSame( 0.0, $summary->parties()[1]->total()->amount() );
		self::assertSame( 100.0, $summary->generalTotal()->amount() );
	}

	public function testAbsentPartyCanReceiveOnlyExplicitExpense(): void {
		$expense = array( new LiveExpense( 'Raccomandata', Money::euros( 12 ) ) );
		$summary = $this->calculate( MediationType::VOLUNTARY, Scenario::ABSENCE, array( $this->party( 'absent', Party::ABSENT, Party::ROLE_INVITED, 1, $expense ) ) );
		self::assertSame( 12.0, $summary->generalTotal()->amount() );
	}

	/**
	 * @dataProvider scenarioProvider
	 */
	public function testScenarioIncrease( string $scenario, float $expected ): void {
		$summary = $this->calculate( MediationType::VOLUNTARY, $scenario, array( $this->party( 'a', Party::PRESENT ) ) );
		self::assertSame( $expected, $summary->generalTotal()->amount() );
	}

	/** @return array<string,array{string,float}> */
	public static function scenarioProvider(): array {
		return array(
			'first no agreement'    => array( Scenario::FIRST_NO_AGREEMENT, 100.0 ),
			'first agreement'       => array( Scenario::FIRST_AGREEMENT, 110.0 ),
			'multiple no agreement' => array( Scenario::MULTIPLE_NO_AGREEMENT, 115.0 ),
			'multiple agreement'    => array( Scenario::MULTIPLE_AGREEMENT, 125.0 ),
			'continuation'          => array( Scenario::CONTINUATION, 105.0 ),
			'mediator proposal'     => array( Scenario::MEDIATOR_PROPOSAL, 120.0 ),
		);
	}

	public function testPaidAmountReducesResidualOnly(): void {
		$summary = $this->calculate( MediationType::VOLUNTARY, Scenario::FIRST_NO_AGREEMENT, array( $this->party( 'a', Party::PRESENT, Party::ROLE_CLAIMANT, 1, array(), 30 ) ) );
		self::assertSame( 100.0, $summary->generalTotal()->amount() );
		self::assertSame( 70.0, $summary->generalResidual()->amount() );
	}

	public function testLiveExpensesBelongToSelectedParty(): void {
		$first = $this->party( 'a', Party::PRESENT, Party::ROLE_CLAIMANT, 1, array( new LiveExpense( 'Firma digitale', Money::euros( 5 ) ) ) );
		$second = $this->party( 'b', Party::PRESENT, Party::ROLE_INVITED );
		$summary = $this->calculate( MediationType::VOLUNTARY, Scenario::FIRST_NO_AGREEMENT, array( $first, $second ) );
		self::assertSame( 105.0, $summary->parties()[0]->total()->amount() );
		self::assertSame( 100.0, $summary->parties()[1]->total()->amount() );
	}

	/** @param array<Party> $parties */
	private function calculate( string $type, string $scenario, array $parties ) {
		return $this->calculator->calculate( new DisputeValue( 5000 ), new MediationType( $type ), new Scenario( $scenario ), $parties );
	}

	/** @param array<LiveExpense> $expenses */
	private function party( string $id, string $status, string $role = Party::ROLE_CLAIMANT, int $centers = 1, array $expenses = array(), float $paid = 0 ): Party {
		$interest_centers = array();
		for ( $index = 1; $index <= $centers; ++$index ) {
			$interest_centers[] = new InterestCenter( (string) $index, 'Centro ' . $index );
		}
		return new Party( $id, ucfirst( $id ), $role, $status, $interest_centers, $expenses, new PaidAmount( Money::euros( $paid ) ) );
	}
}
