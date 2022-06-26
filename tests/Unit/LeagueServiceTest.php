<?php

namespace Tests\Unit;

use App\Models\Fixture;
use App\Models\LeagueStage;
use App\Models\Stats;
use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LeagueServiceTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var LeagueStage|MockObject
     */
    private $leagueStageBuilderStub;
    /**
     * @var Stats
     */
    private $statsBuilderStub;
    /**
     * @var Fixture|MockObject
     */
    private $fixturesBuilderStub;
    /**
     * @var Team|MockObject
     */
    private $teamsBuilderStub;
    /**
     * @var Kernel|MockObject
     */
    private $kernelStub;
    /**
     * @var LeagueService
     */
    private LeagueService $triggeredService;
    /**
     * @var Connection|MockObject
     */
    private MockObject $connectionStub;

    protected function setUp(): void
    {
        //TODO: add exceptions coverage
        parent::setUp();

        $this->leagueStageBuilderStub = $this->getMockBuilder(LeagueStage::class)->getMock();
        $this->statsBuilderStub = $this->getMockBuilder(Stats::class)->getMock();
        $this->fixturesBuilderStub = $this->getMockBuilder(Fixture::class)->getMock();
        $this->teamsBuilderStub = $this->getMockBuilder(Team::class)->getMock();
        $this->kernelStub = $this->getMockBuilder(Kernel::class)->disableOriginalConstructor()->getMock();
        $this->connectionStub = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $this->triggeredService = new LeagueService(
            $this->leagueStageBuilderStub,
            $this->statsBuilderStub,
            $this->fixturesBuilderStub,
            $this->teamsBuilderStub,
            $this->connectionStub,
            $this->kernelStub
        );
    }

    public function testTeamStatsFound()
    {
        $this->statsBuilderStub
            ->expects(self::once())
            ->method('getAllStats')
            ->willReturn(new Collection());
        $this->triggeredService->getTeamStats();
    }

    public function testLastPlayedStats()
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getLastPlayedStage')
            ->willReturn($this->getLeagueStageStub(true));
        $this->triggeredService->getLastPlayedStats();
    }

    public function testPlayAll()
    {
        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('getStagesToPlay')
            ->willReturn((new Collection())->add($this->getLeagueStageStub(true))->add($this->getLeagueStageStub(true)));

        $this->fixturesBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processPush')
            ->willReturn(true);

        $this->fixturesBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->fixturesBuilderStub
            ->expects(self::atLeastOnce())
            ->method('getFixturesByIds')
            ->willReturn((new Collection())->add($this->getFixtureStub())->add($this->getFixtureStub()));

        //TODO:check never - coverage
        $this->statsBuilderStub
            ->expects(self::never())
            ->method('processSave')
            ->willReturn(true);

        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->triggeredService->playAllStages();
    }

    public function testPlayNextStage()
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getNextStageToPlay')
            ->willReturn($this->getLeagueStageStub(true));

        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('getStagesToPlay')
            ->willReturn((new Collection())->add($this->getLeagueStageStub(true))->add($this->getLeagueStageStub(true)));

        $this->fixturesBuilderStub
            ->expects(self::atLeastOnce())
            ->method('getFixturesByIds')
            ->willReturn((new Collection())->add($this->getFixtureStub())->add($this->getFixtureStub()));

        //TODO:check never - coverage
        $this->statsBuilderStub
            ->expects(self::never())
            ->method('processSave')
            ->willReturn(true);

        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->triggeredService->playNextStage();
    }

    public function testAllGroupedFixtures()
    {
        $this->fixturesBuilderStub
            ->expects(self::once())
            ->method('getFixturesGroupedByStages')
            ->willReturn(new Collection());
        $this->triggeredService->getAllFixtures();
    }

    /**
     * @param bool $withFixtures
     * @return LeagueStage
     */
    private function getLeagueStageStub(bool $withFixtures = false): LeagueStage
    {
        $leagueStageStub = new LeagueStage();
        $leagueStageStub->id = mt_rand();
        $leagueStageStub->name = 'Week ' . mt_rand();
        if ($withFixtures) {
            $leagueStageStub->fixtures = [$this->getFixtureStub(), $this->getFixtureStub()];
        }

        return $leagueStageStub;
    }

    /**
     * @return Fixture
     */
    private function getFixtureStub(): Fixture
    {
        $fixtureStub = new Fixture();
        $fixtureStub->homeTeam = $this->getTeamStub();
        $fixtureStub->awayTeam = $this->getTeamStub();
        $fixtureStub->stage = $this->getLeagueStageStub();

        return $fixtureStub;
    }

    /**
     * @return Team
     */
    private function getTeamStub(): Team
    {
        $teamStub = new Team();
        $teamStub->name = 'Team' . mt_rand();
        $teamStub->stats = $this->getStatsStub();

        return $teamStub;
    }

    /**
     * @return Stats
     */
    private function getStatsStub(): Stats
    {
        $statsStub = new Stats();
        $statsStub->power = mt_rand(1, 5);
        $statsStub->points = mt_rand(1, 5);

        return $statsStub;
    }

}
