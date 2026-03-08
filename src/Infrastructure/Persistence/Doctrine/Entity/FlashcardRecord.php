<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'flashcards')]
#[ORM\Index(name: 'idx_flashcards_deck_id', columns: ['deck_id'])]
final class FlashcardRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'deck_id', type: 'string', length: 40)]
    private string $deckId;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type = 'QA';

    #[ORM\Column(type: 'text')]
    private string $question;

    #[ORM\Column(type: 'text')]
    private string $answer;

    /** @var array<int, string> */
    #[ORM\Column(type: 'json')]
    private array $options = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getDeckId(): string
    {
        return $this->deckId;
    }

    public function setDeckId(string $deckId): void
    {
        $this->deckId = $deckId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @return array<int, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<int, string> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = array_values($options);
    }
}
