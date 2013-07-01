<?php
namespace Kunstmaan\TranslatorBundle\Tests\Service\Importer;

use Kunstmaan\TranslatorBundle\Tests\BaseTestCase;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;

class ImporterTest extends BaseTestCase
{

    private $importer;
    private $translationRepository;

    public function setUp()
    {
        parent::setUp();
        $this->importer = $this->getContainer()->get('kunstmaan_translator.service.importer.importer');
        $this->translationRepository = $this->getContainer()->get('kunstmaan_translator.repository.translation');
    }

    /**
     * @group importer
     */
    public function testImportNewDomainFileNonForced()
    {
        foreach ($this->getNewDomainTestFinder() as $file) {
            $this->importer->import($file, false);
        }

        $translation = $this->translationRepository->findOneBy(array('keyword' => 'headers.frontpage', 'locale' => 'en'));
        $this->assertEquals('a not yet updated frontpage header', $translation->getText());
    }

     /**
     * @group importer-forced
     */
    public function testImportNewDomainFileForced()
    {
        foreach ($this->getNewDomainTestFinder() as $file) {
            $this->importer->import($file, true);
        }

        $translation = $this->translationRepository->findOneBy(array('keyword' => 'headers.frontpage', 'locale' => 'en'));
        $this->assertEquals('FrontPage', $translation->getText());
    }

    public function getNewDomainTestFinder()
    {
        $finder = new Finder;

        $finder->files()
                ->name('messages.en.yml')
                ->in($this->getContainer()->getParameter('kernel.root_dir').'/Resources/translations/');

        return $finder;
    }
}