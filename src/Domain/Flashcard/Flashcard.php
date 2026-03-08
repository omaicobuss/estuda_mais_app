<?php

declare(strict_types=1);

namespace App\Domain\Flashcard;

final class Flashcard
{
    private const VALID_TYPES = ['QA', 'MULTIPLE', 'TRUE_FALSE'];

    /**
     * @param array<int, string> $options
     */
    public function __construct(
        private string $id,
        private string $deckId,
        private string $type,
        private string $question,
        private string $answer,
        private array $options
    ) {
    }

    /**
     * @param array<int, string> $options
     */
    public static function create(
        string $id,
        string $deckId,
        string $type,
        string $question,
        string $answer,
        array $options = []
    ): self {
        $normalizedType = strtoupper(trim($type));
        if (!in_array($normalizedType, self::VALID_TYPES, true)) {
            $normalizedType = 'QA';
        }

        return new self(
            $id,
            $deckId,
            $normalizedType,
            trim($question),
            trim($answer),
            array_values($options)
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['deck_id'],
            (string) $data['type'],
            (string) $data['question'],
            (string) $data['answer'],
            array_values($data['options'] ?? [])
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'deck_id' => $this->deckId,
            'type' => $this->type,
            'question' => $this->question,
            'answer' => $this->answer,
            'options' => $this->options,
        ];
    }

    public function id(): string
    {
        return $this->id;
    }

    public function deckId(): string
    {
        return $this->deckId;
    }

    public function question(): string
    {
        return $this->question;
    }

    public function answer(): string
    {
        return $this->answer;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array<int, string>
     */
    public function options(): array
    {
        return $this->options;
    }
}
