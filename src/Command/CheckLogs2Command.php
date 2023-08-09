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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'app:check-logs-2',
    description: 'Add a short description for your command',
)]
class CheckLogs2Command extends Command
{
    public function __construct(private LogsRepository $logsRepository,
                                private HttpClientInterface $httpClient,
                                private Request $request,
                                private Response $response,
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
        $logs = $this->logsRepository->findWiększeNiżIdIStatusieError($id);

        if(count($logs) === 0) {
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
            $czyZgłoszony = $this->logsRepository->findCzyLogWystepujeDzis($today, $log->getMessage(), $log->getId());
            if($czyZgłoszony) {
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
        $token = "yOJ9KvRtQrTUaCPyRc22OJPMSmIrub9PUzMFUZEHMgXcd1fRWP7pBfosdSDFLzOF";
        $req = new Request()
        $response = $this->httpClient->request('POST', 'http://localhost:8000/', ['head' => ['token' => $token], 'body' => [$logs]]);
        $op = $response->getInfo();
        return ($response->getStatusCode() == 200) ? true : false;
    }
    
    private function zaktualizujIdOstatniegoPrzetworzonegoRekorduZBazy($logs): void
    {
        $log = array_pop($logs);
        $id = $log->getId();
        //file_put_contents("last_id", $id);
    }
    
}
/* {
    "data_wyslania":"2023-12-24 11:50:13",
    "token":"987654321",
    "status":"1",
    "logi":"~body"
}   */