<?php

namespace Michaeljennings\Laralastica\Commands;

use Illuminate\Console\Command;
use Michaeljennings\Laralastica\Indexer;

class ReIndexLaralastica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laralastica:index {index?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-indexes all of the searchable models';

    /**
     * The elasticsearch indexing service.
     *
     * @var Indexer
     */
    protected $indexer;

    /**
     * Create a new command instance.
     *
     * @param Indexer $indexer
     */
    public function __construct(Indexer $indexer)
    {
        parent::__construct();

        $this->indexer = $indexer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->indexer->index($this->argument('index'));
    }
}
