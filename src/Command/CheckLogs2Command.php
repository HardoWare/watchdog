<?php

namespace App\Command;

use App\Repository\LogsRepository;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:check-logs-2',
    description: 'Add a short description for your command',
)]
class CheckLogs2Command extends Command
{
    public function __construct(private readonly LogsRepository      $logsRepository,
                                private readonly HttpClientInterface $httpClient,
    )
    {
        parent::__construct();
    }
    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $this->zwrocIdOstatniegoPrzetworzonegoRekorduZBazy();
        $logs = $this->logsRepository->findWiekszeNizIdIStatusieError($id);

        if(count($logs) === 0) {
            // request
            $io->success("Brak logów do przesłania");
            return Command::SUCCESS;
        }


        $logs = $this->usunWyslaneLogi($logs);
        $this->wyswietlLogiDoPrzeslania($logs, $output);

        if($this->wyslijRequest($logs)) {
            $this->zaktualizujIdOstatniegoPrzetworzonegoRekorduZBazy($logs);
            $io->success('Wszystkie logi zostały przetworzone prawidłowo.');
            return Command::SUCCESS;
        }

        $io->warning('Brak Odpowiedzi serwera, nie można było zaktualizować logów.');
        return Command::FAILURE;
    }

    private function zwrocIdOstatniegoPrzetworzonegoRekorduZBazy(): int
    {
        if(file_exists("last_id")) {
            $id = file_get_contents("last_id");
            if(!empty($id) && is_int($id)) {
                return $id;
            }
        }
        return 1;
    }

    private function usunWyslaneLogi($logs): array
    {
        $today = new DateTime("today");
        $i = 0;
        foreach($logs as $log) {
            $czyZgloszony = $this->logsRepository->findCzyLogWystepujeDzis($today, $log->getMessage(), $log->getId());
            if($czyZgloszony) {
                unset($logs[$i]);
            }
            $i++;
        }
        return $logs;
    }

    private function wyswietlLogiDoPrzeslania($logs, $output): void
    {
        $rows = [];
        $table = new Table($output);
        $table->setHeaders(['ID','Time Stamp','Status','Message']);
        foreach($logs as $log) {
            $rows[] = [$log->getId(), date('Y-m-d H:i:s', $log->getTimeStamp()->getTimeStamp()), $log->getStatus(), $log->getMessage()];
        }
        $table->setRows($rows);
        $table->render();
    }

    private function wyslijRequest($logs): bool
    {
        $hostName = "HOST_0";
        $hostToken = "0_uni64d4ba2273871";
        $cuki = "XDEBUG_SESSION=PHPSTORM";
        $arr = [];
        foreach ($logs as $log) {
            $arr[] = ['id' => $log->getId(), 'time_stamp' => $log->getTimeStamp(), 'status' => $log->getStatus(), 'message' => $log->getMessage()];
        }

        $response = $this->httpClient->request('POST',"http://localhost:9900/api/{$hostName}", [
            'headers' => [
                'X-REMOTE-HOST'         => $hostToken,
                'Content-Type'          => 'application/json',
                'Cookie'                => $cuki,
            ],
            'json' => $arr
        ]);

        return $response->getStatusCode() === 200;
    }
    
    private function zaktualizujIdOstatniegoPrzetworzonegoRekorduZBazy($logs): void
    {
        $log = array_pop($logs);
        $id = $log->getId();
        //file_put_contents("last_id", $id);
    }
    
}
/*
    json
[{
	"id": 2,
	"time_stamp": {
		"date": "2023-08-15 17:35:36.000000",
		"timezone_type": 3,
		"timezone": "Europe\/Berlin"
	},
	"status": 1,
	"message": "86"
}, {
	"id": 4,
	"time_stamp": {
		"date": "2023-08-15 17:35:36.000000",
		"timezone_type": 3,
		"timezone": "Europe\/Berlin"
	},
	"status": 1,
	"message": "84"
}, {
	"id": 6,
	"time_stamp": {
		"date": "2023-08-15 17:35:36.000000",
		"timezone_type": 3,
		"timezone": "Europe\/Berlin"
	},
	"status": 1,
	"message": "27"
}, {
	"id": 8,
	"time_stamp": {
		"date": "2023-08-15 17:35:36.000000",
		"timezone_type": 3,
		"timezone": "Europe\/Berlin"
	},
	"status": 1,
	"message": "47"
}, {
	"id": 10,
	"time_stamp": {
		"date": "2023-08-15 17:35:36.000000",
		"timezone_type": 3,
		"timezone": "Europe\/Berlin"
	},
	"status": 1,
	"message": "61"
}]

 */