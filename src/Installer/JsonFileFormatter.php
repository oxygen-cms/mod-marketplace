<?php


namespace OxygenModule\Marketplace\Installer;

use Composer\IO\WorkTracker\AbstractWorkTracker;
use Composer\IO\WorkTracker\BoundWorkTracker;
use Composer\IO\WorkTracker\Formatter\DebouncedFormatter;
use Composer\IO\WorkTracker\FormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JsonFileFormatter extends DebouncedFormatter implements FormatterInterface {

    protected $previousFullProgress = 0;
    protected $operationHeuristics;
    protected $output;
    protected $data;
    protected $file;

    public static $noMessage = '';

    public function __construct($file, OutputInterface $output, array $heuristics) {
        parent::__construct();
        $this->output = $output;
        $this->file = $file;
        $this->data = [];
        $this->previousFullProgress = 0;
        $this->operationHeuristics = $heuristics;
    }

    /**
     * Called when work tracker is created
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function create(AbstractWorkTracker $workTracker) {
        if($workTracker->getDepth() == 1) {
            $this->setMainMessage($workTracker->getTitle());
            $this->setMessage(static::$noMessage);
        } else {
            $this->setMessage($workTracker->getTitle());
        }

        $this->writeChanges();
    }

    /**
     * Called when work tracker is completed
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function complete(AbstractWorkTracker $workTracker) {
        if($workTracker->getDepth() == 0) {
            $this->finish();
            return;
        } else if($workTracker->getDepth() == 1) {
            $this->previousFullProgress += $this->getWeightForTitle($workTracker->getTitle());
            $this->setProgress($this->previousFullProgress);
        } else if($workTracker->getDepth() == 2) {
            //$this->progressBar->setMessage(static::$noMessage);
        } else {
            $this->setMessage($workTracker->getParent()->getTitle());
        }

        $this->writeChanges();
    }

    /**
     * Called when the work tracker is "pinged" (notified of
     * some progress).
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function ping(AbstractWorkTracker $workTracker) {
        if(!$this->shouldDisplayAgain()) {
            return;
        }

        // this algorithm estimates a total progress by accumulating the status of several nested work trackers
        $progress = 0;
        $title = null;
        while ($parent = $workTracker->getParent()) {

            if ($workTracker instanceof BoundWorkTracker) {
                $progress /= $workTracker->getMax();
                $progress += $workTracker->getPingCount() / $workTracker->getMax();
            } else {
                $progress = 0;
            }

            if($workTracker->getDepth() == 1) {
                $title = $workTracker->getTitle();
            }

            $workTracker = $parent;
        }

        $progress = $this->previousFullProgress + ($progress * $this->getWeightForTitle($title));
        $this->setProgress($progress);
        $this->writeChanges();
    }

    private function getWeightForTitle($title) {
        $title = trim(strip_tags($title));
        if(isset($this->operationHeuristics['weights'][$title])) {
            return $this->operationHeuristics['weights'][$title] / 100;
        } else {
            $this->output->writeln("Invalid step name " . $title);
            return 0.01; // 1%
        }
    }

    private function setProgress($progress) {
        $this->data['progress'] = $progress;
    }

    private function setMessage($message) {
        $this->data['message'] = $message;
    }

    private function setMainMessage($message) {
        $this->data['sectionMessage'] = $message;
    }

    public function finish() {
        $this->data['finished'] = true;
    }

    public function notification($message, $status) {
        $this->data['notification'] = [
            'content' => $message,
            'status'  => $status,
            'unique'  => rand() // used to only display notifications if they are new
        ];
    }

    /**
     * Writes the information out to a file.
     */
    public function writeChanges() {
        file_put_contents($this->file, json_encode($this->data));
    }

}