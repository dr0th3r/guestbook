<?php

namespace App\Tests;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    public function testCommentSubmission()
    {
        $client = static::createClient();
        $client->request('GET', '/conference/paris-2023');
        $client->submitForm('Submit', [
            'comment[author]' => 'Tester',
            'comment[text]' => 'Some feedback from an automated functional test',
            'comment[email]' => $email = 'tester@test.com',
            'comment[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.gif',
        ]);
        $this->assertResponseRedirects();

        $comment = self::getContainer()->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState('published');
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }

    public function testConferencePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Paris 2023');
        $this->assertSelectorTextContains('h2', 'Paris 2023');
        $this->assertSelectorExists('div:contains("No comments have been posted yet for this conference.")');
    }
}
