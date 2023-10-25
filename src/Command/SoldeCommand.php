<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ValidationDocumentsCommand.
 */
class SoldeCommand extends Command
{
    protected static $defaultName = 'app:enable_solde:users';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function configure()
    {
        $this
            ->setDescription('Régles  de saisie des compteurs des congés ')
            ->setHelp('cette commande permet d\'ajouter un solde du congés pour chaque employé' )
        ;
    }

    /**
     * StatusDrugCommand constructor.
     *
     * @param EntityManagerInterface $em         EntityManagerInterface
     * @param ParameterBagInterface  $parameters ParameterBagInterface
     */
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        parent::__construct();

        $this->em = $em;
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input  InputInterface
     * @param OutputInterface $output OutputInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Log
        $io = new SymfonyStyle($input, $output);
        $io->title('start generations du solde de congé');

        $users = $this->em->getRepository(User::class)->findAll();

        $io->progressStart(count($users));

        foreach ($users as $user)
        {

            $user->setSolde($user->getSolde() + 1.5);

            $this->em->persist($user);
            $this->em->flush();

            $io->progressAdvance();
        }

        // Success
        $io->success('validation success');

        return 0;
    }
}
