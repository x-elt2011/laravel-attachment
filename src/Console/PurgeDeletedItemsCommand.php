<?php

namespace Xelt2011\Attachment\Console;

use Illuminate\Console\Command;
use Xelt2011\Attachment\Attachment;
use Storage;
use DB;
use Exception;

class PurgeDeletedItemsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attachment:purge-deleted-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove physical files for deleted items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		DB::beginTransaction();
		try {
			Attachment::onlyTrashed()->chunk(100, function ($attachments) {
				$attachments->each(function ($attachment) {
					$attachment->forceDelete();
				});
			});
			DB::commit();
		} catch (Exception $ex) {
			DB::rollBack();
		}
    }
}
