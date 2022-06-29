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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LeagueServiceTest extends TestCase
{
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

    public function testTeamStatsFound(): void
    {
        $this->statsBuilderStub
            ->expects(self::once())
            ->method('getAllStats')
            ->willReturn(new Collection());
        $this->triggeredService->getTeamStats();
    }

    public function testLastPlayedStats(): void
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getLastPlayedStage')
            ->willReturn($this->getLeagueStageStub(true));
        $this->triggeredService->getLastPlayedStats();
    }

    public function testPlayAll(): void
    {
        $this->leagueStageBuilderStub
            ->expects(self::exactly(3))
            ->method('getStagesToPlay')
            ->willReturn((new Collection())->add($this->getLeagueStageStub(true))->add($this->getLeagueStageStub(true)));

        $this->statsBuilderStub
            ->expects(self::exactly(2))
            ->method('getAllStats')
            ->willReturn((new Collection())->add($this->getStatsStub())->add($this->getStatsStub()));

        $this->fixturesBuilderStub
            ->expects(self::exactly(2))
            ->method('getFixturesByIds')
            ->willReturn((new Collection())->add($this->getFixtureStub())->add($this->getFixtureStub()));

        $this->mockPushSaveRepo(self::atLeastOnce());

        $this->triggeredService->playAllStages();
    }

    public function testNoStagesPlayAll(): void
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getStagesToPlay')
            ->willReturn((new Collection()));

        $this->fixturesBuilderStub
            ->expects(self::never())
            ->method('getFixturesByIds');

        $this->mockPushSaveRepo(self::never());

        try {
            $this->triggeredService->playAllStages();
        } catch (\Throwable $e) {
            $this->assertStringContainsString('No league stages to play', $e->getMessage());
        }
    }

    public function testPlayNextStage(): void
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getNextStageToPlay')
            ->willReturn($this->getLeagueStageStub(true));

        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getStagesToPlay')
            ->willReturn((new Collection())->add($this->getLeagueStageStub(true))->add($this->getLeagueStageStub(true)));

        $this->statsBuilderStub
            ->expects(self::once())
            ->method('getAllStats')
            ->willReturn((new Collection())->add($this->getStatsStub())->add($this->getStatsStub()));

        $this->fixturesBuilderStub
            ->expects(self::once())
            ->method('getFixturesByIds')
            ->willReturn((new Collection())->add($this->getFixtureStub())->add($this->getFixtureStub()));

        $this->statsBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->triggeredService->playNextStage();
    }

    public function testNoStagesPlayNext(): void
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getNextStageToPlay')
            ->willReturn(null);

        $this->fixturesBuilderStub
            ->expects(self::never())
            ->method('getFixturesByIds');

        $this->mockPushSaveRepo(self::never());

        try {
            $this->triggeredService->playNextStage();
        } catch (\Throwable $e) {
            $this->assertStringContainsString('No league stage to play', $e->getMessage());
        }
    }

    public function testSetLeagueWinner(): void
    {
        $this->leagueStageBuilderStub
            ->expects(self::once())
            ->method('getNextStageToPlay')
            ->willReturn($this->getLeagueStageStub(true));

        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('getStagesToPlay')
            ->willReturn((new Collection()));

        $this->fixturesBuilderStub
            ->expects(self::never())
            ->method('getFixturesByIds')
            ->willReturn((new Collection())->add($this->getFixtureStub())->add($this->getFixtureStub()));

        $this->statsBuilderStub
            ->expects(self::once())
            ->method('getAllStats')
            ->willReturn((new Collection())->add($this->getStatsStub())->add($this->getStatsStub()));

        $this->statsBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->leagueStageBuilderStub
            ->expects(self::atLeastOnce())
            ->method('processSave')
            ->willReturn(true);

        $this->triggeredService->playNextStage();
    }

    public function testAllGroupedFixtures(): void
    {
        $this->fixturesBuilderStub
            ->expects(self::once())
            ->method('getFixturesGroupedByStages')
            ->willReturn(new Collection());
        $this->triggeredService->getAllFixtures();
    }

    private function mockPushSaveRepo($frequency): void
    {
        $this->fixturesBuilderStub
            ->expects($frequency)
            ->method('processPush')
            ->willReturn(true);

        $this->fixturesBuilderStub
            ->expects($frequency)
            ->method('processSave')
            ->willReturn(true);

        $this->statsBuilderStub
            ->expects($frequency)
            ->method('processSave')
            ->willReturn(true);

        $this->leagueStageBuilderStub
            ->expects($frequency)
            ->method('processSave')
            ->willReturn(true);
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
