<?php

namespace App\Command;

use App\Entity\Logs;
use App\Repository\LogsRepository;
use DateTime;
use ErrorException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AsCommand(
    name: 'app:check-logs',
    description: 'Add a short description for your command',
)]
class CheckLogsCommand extends Command
{
    public function __construct(private LogsRepository $logsRepository, private HttpClientInterface $httpClient)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setDefinition(
                new InputDefinition([
                    new InputOption('time', 't', InputOption::VALUE_REQUIRED, "Od ilu godzin sprawdzać logi", 1),
                ])
                );
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $time = $input->getOption('time');
        $date = new DateTime("$time hours ago");
        $logs = $this->logsRepository->findNowszeNiżIOStatusieError();
        
        if(count($logs) === 0) {
            $io->success("Brak logów do przesłania");
            return Command::SUCCESS;
        }

        $this->wyswietlLogiDoPrzeslania($logs, $output);
        $json = $this->filtrojNiedawnoPrzeslaneLogi($logs);
        $json = json_encode($json);

        if($this->wyslijRequest($json)) {
            foreach($logs as $log) {
                $this->logsRepository->setStatusWysłano($log->getId());
            }
            $io->success('Wszystkie logi zostały przetworzone prawidłowo.');
            return Command::SUCCESS;
        }
        
        $io->warning('Brak Odpowiedzi serwera, nie można było zaktualizować logów.');
        return Command::FAILURE;
    }
    private function wyswietlLogiDoPrzeslania($logs, $output): void
    {
        $rows = [];
        $table = new Table($output);
        $table->setHeaders(['ID','Time Stamp','Status','Message']);
        foreach($logs as $log) {
            $rows[] = [$log->getId(),date('Y-m-d H:i:s',$log->getTimeStamp()->getTimeStamp()),$log->getStatus().' -> 2',$log->getMessage()];
        }
        $table->setRows($rows);
        $table->render();
    }
    private function filtrojNiedawnoPrzeslaneLogi($logs): array
    {
        $tab = [];
        $today = new DateTime("today");
        foreach($logs as $log) {
            $czyZgłoszony = $this->logsRepository->findCzyLogWystepujeDzis($today, $log->getMessage(), $log->getId());
            if(!$czyZgłoszony) {
                $tab[] = ['id' => $log->getId(),'time_stamp' => $log->getTimeStamp()->getTimeStamp(),'status' => $log->getStatus(),'message' => $log->getMessage()];
            }
        }
        return $tab;
    }
    private function wyslijRequest($json): bool
    {
        $token = "yOJ9KvRtQrTUaCPyRc22OJPMSmIrub9PUzMFUZEHMgXcd1fRWP7pBfosdSDFLzOF";
        $response = $this->httpClient->request('POST', 'http://localhost:9999/', ['body' => $json, 'query' => ['token' => $token]]);
        return ($response->getStatusCode() == 200) ? true : false;
    }
}
/* {
    "data_wyslania":"2023-12-24 11:50:13",
    "token":"987654321",
    "status":"1",
    "logi":"~body"
}   */