<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai;

use App\Application\Contracts\AiCardGeneratorInterface;

final class TemplateAiCardGenerator implements AiCardGeneratorInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function generate(string $topic, int $count, ?string $sourceText = null): array
    {
        $normalizedTopic = trim($topic) !== '' ? trim($topic) : 'Estudo geral';
        $size = max(1, min(20, $count));
        $snippets = $this->snippets($sourceText);

        $cards = [];
        for ($i = 0; $i < $size; $i++) {
            $snippet = $snippets[$i % max(1, count($snippets))];
            $mode = $i % 3;

            if ($mode === 0) {
                $cards[] = [
                    'type' => 'QA',
                    'question' => sprintf('Explique %s em linguagem simples.', $normalizedTopic),
                    'answer' => sprintf('Resumo essencial: %s', $snippet),
                    'options' => [],
                ];
                continue;
            }

            if ($mode === 1) {
                $cards[] = [
                    'type' => 'TRUE_FALSE',
                    'question' => sprintf(
                        'Verdadeiro ou falso: %s pode ser aplicado em um exercicio pratico.',
                        $normalizedTopic
                    ),
                    'answer' => 'Verdadeiro',
                    'options' => ['Verdadeiro', 'Falso'],
                ];
                continue;
            }

            $cards[] = [
                'type' => 'MULTIPLE',
                'question' => sprintf('Qual alternativa melhor representa %s?', $normalizedTopic),
                'answer' => 'Conceito principal',
                'options' => [
                    'Conceito principal',
                    'Detalhe secundario',
                    'Exemplo irrelevante',
                    'Fato desconectado',
                ],
            ];
        }

        return $cards;
    }

    /**
     * @return array<int, string>
     */
    private function snippets(?string $sourceText): array
    {
        $text = trim((string) $sourceText);
        if ($text === '') {
            return [
                'identificar definicao, exemplo e aplicacao pratica',
                'conectar teoria com exercicios curtos',
                'revisar erros frequentes e pontos chave',
            ];
        }

        $parts = preg_split('/[.\n\r;!?]+/', $text) ?: [];
        $snippets = [];
        foreach ($parts as $part) {
            $candidate = trim($part);
            if ($candidate === '') {
                continue;
            }

            $snippets[] = substr($candidate, 0, 120);
            if (count($snippets) >= 6) {
                break;
            }
        }

        return $snippets !== [] ? $snippets : ['revisar os pontos centrais do tema'];
    }
}
