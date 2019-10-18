<?php
/**
 * Created by PhpStorm.
 * User: Manlin
 * Date: 2019/9/18
 * Time: 下午1:28
 */
namespace EasySwoole\Kafka\Consumer;

use EasySwoole\Component\Singleton;
use EasySwoole\Kafka\Broker;
use function count;

class Assignment
{
    use Singleton;

    /**
     * @var string
     */
    private $memberId = '';

    /**
     * @var int|null
     */
    private $generationId;

    /**
     * @var int[][][][]
     */
    private $assignments = [];

    /**
     * @var mixed[][]
     */
    private $topics = [];

    /**
     * 初始偏移量
     * @var int[][]
     */
    private $offsets = [];

    /**
     * 分区中可支持的最大的偏移量
     * @var int[][]
     */
    private $lastOffsets = [];

    /**
     * fetchOffset返回的对应偏移量
     * @var int[][]
     */
    private $fetchOffsets = [];

    /**
     * 当前消费偏移量
     * @var int[][]
     */
    private $consumerOffsets = [];

    /**
     * 当前提交偏移量
     * @var int[][]
     */
    private $commitOffsets = [];

    /**
     * @var int[][]
     */
    private $preCommitOffsets = [];

    public function setMemberId(string $memberId): void
    {
        $this->memberId = $memberId;
    }

    public function getMemberId(): string
    {
        return $this->memberId;
    }

    public function setGenerationId(int $generationId): void
    {
        $this->generationId = $generationId;
    }

    public function getGenerationId(): ?int
    {
        return $this->generationId;
    }

    /**
     * @return int[][][][]
     */
    public function getAssignments(): array
    {
        return $this->assignments;
    }

    /**
     * @param array $assignments
     */
    public function setAssignments(array $assignments): void
    {
        $this->assignments = $assignments;
    }

    /**
     * @param string[] $result
     */
    public function assign(array $result): void
    {
        /** @var Broker $broker */
        $broker = Broker::getInstance();
        $topics = $broker->getTopics();

        $memberCount = count($result);

        $count   = 0;
        $members = [];

        foreach ($topics as $topicName => $partition) {
            foreach ($partition as $partitionId => $leaderId) {
                $memberNum = $count % $memberCount;

                if (! isset($members[$memberNum])) {
                    $members[$memberNum] = [];
                }

                if (! isset($members[$memberNum][$topicName])) {
                    $members[$memberNum][$topicName] = [];
                }

                $members[$memberNum][$topicName]['topic_name'] = $topicName;

                if (! isset($members[$memberNum][$topicName]['partitions'])) {
                    $members[$memberNum][$topicName]['partitions'] = [];
                }

                $members[$memberNum][$topicName]['partitions'][] = $partitionId;
                ++$count;
            }
        }

        $data = [];

        foreach ($result as $key => $member) {
            $data[] = [
                'version'     => 0,
                'member_id'   => $member['memberId'],
                'assignments' => $members[$key] ?? [],
            ];
        }

        $this->setAssignments($data);
    }

    /**
     * @param mixed[][] $topics
     */
    public function setTopics(array $topics): void
    {
        $this->topics = $topics;
    }

    /**
     * @return mixed[][]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * @param int[][] $offsets
     */
    public function setOffsets(array $offsets): void
    {
        $this->offsets = $offsets;
    }

    /**
     * @return int[][]
     */
    public function getOffsets(): array
    {
        return $this->offsets;
    }

    /**
     * @param int[][] $offsets
     */
    public function setLastOffsets(array $offsets): void
    {
        $this->lastOffsets = $offsets;
    }

    /**
     * @return int[][]
     */
    public function getLastOffsets(): array
    {
        return $this->lastOffsets;
    }

    /**
     * @param int[][] $offsets
     */
    public function setFetchOffsets(array $offsets): void
    {
        $this->fetchOffsets = $offsets;
    }

    /**
     * @return int[][]
     */
    public function getFetchOffsets(): array
    {
        return $this->fetchOffsets;
    }

    /**
     * @param int[][] $offsets
     */
    public function setConsumerOffsets(array $offsets): void
    {
        $this->consumerOffsets = $offsets;
    }

    /**
     * @return int[][]
     */
    public function getConsumerOffsets(): array
    {
        return $this->consumerOffsets;
    }

    public function setConsumerOffset(string $topic, int $partition, int $offset): void
    {
        $this->consumerOffsets[$topic][$partition] = $offset;
    }

    public function getConsumerOffset(string $topic, int $partition): ?int
    {
        if (! isset($this->consumerOffsets[$topic][$partition])) {
            return null;
        }

        return $this->consumerOffsets[$topic][$partition];
    }

    /**
     * @param int[][] $offsets
     */
    public function setCommitOffsets(array $offsets): void
    {
        $this->commitOffsets = $offsets;
    }

    /**
     * @return int[][]
     */
    public function getCommitOffsets(): array
    {
        return $this->commitOffsets;
    }

    public function setCommitOffset(string $topic, int $partition, int $offset): void
    {
        $this->commitOffsets[$topic][$partition] = $offset;
    }

    /**
     * @param int[][] $offsets
     */
    public function setPreCommitOffsets(array $offsets): void
    {
        $this->preCommitOffsets = $offsets;
    }

    /**
     * @return int[][]
     */
    public function getPreCommitOffsets(): array
    {
        return $this->preCommitOffsets;
    }

    public function setPreCommitOffset(string $topic, int $partition, int $offset): void
    {
        $this->preCommitOffsets[$topic][$partition] = $offset;
    }

    public function clearOffset(): void
    {
        $this->offsets          = [];
        $this->lastOffsets      = [];
        $this->fetchOffsets     = [];
        $this->consumerOffsets  = [];
        $this->commitOffsets    = [];
        $this->preCommitOffsets = [];
    }
}