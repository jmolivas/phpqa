<?php
/**
 * @file
 * Contains \Drupal\Console\Core\Style\DrupalStyle.
 */

namespace JMOlivas\Phpqa\Style;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle as SymfonyStyleBase;
use Symfony\Component\Console\Helper\Table;

/**
 * Class DrupalStyle
 *
 * @package Drupal\Console\Core\Style
 */
class SymfonyStyle extends SymfonyStyleBase
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        parent::__construct($input, $output);
    }

    /**
     * @param string $question
     * @param array  $choices
     * @param mixed  $default
     * @param bool   $allowEmpty
     *
     * @return string
     */
    public function choiceNoList($question, array $choices, $default = null, $allowEmpty = false)
    {
        if ($allowEmpty) {
            $default = ' ';
        }

        if (is_null($default)) {
            $default = current($choices);
        }

        if (!in_array($default, $choices)) {
            $choices[] = $default;
        }

        if (null !== $default) {
            $values = array_flip($choices);
            $default = $values[$default];
        }

        return trim($this->askChoiceQuestion(new ChoiceQuestion($question, $choices, $default)));
    }

    /**
     * @param string $question
     * @param array  $choices
     * @param null   $default
     * @param bool   $multiple
     *
     * @return string
     */
    public function choice($question, array $choices, $default = null, $multiple = false)
    {
        if (null !== $default) {
            $values = array_flip($choices);
            $default = $values[$default];
        }

        $choiceQuestion = new ChoiceQuestion($question, $choices, $default);
        $choiceQuestion->setMultiselect($multiple);

        return $this->askQuestion($choiceQuestion);
    }

    /**
     * @param ChoiceQuestion $question
     *
     * @return string
     */
    public function askChoiceQuestion(ChoiceQuestion $question)
    {
        $questionHelper = new QuestionHelper();
        $answer = $questionHelper->ask($this->input, $this, $question);

        return $answer;
    }

    /**
     * @param $question
     *
     * @return string
     */
    public function askHiddenEmpty($question)
    {
        $question = new Question($question, ' ');
        $question->setHidden(true);

        return trim($this->askQuestion($question));
    }

    /**
     * @param string        $question
     * @param null|callable $validator
     *
     * @return string
     */
    public function askEmpty($question, $validator = null)
    {
        $question = new Question($question, ' ');
        $question->setValidator($validator);

        return trim($this->askQuestion($question));
    }

    /**
     * @param $message
     * @param bool    $newLine
     */
    public function info($message, $newLine = true)
    {
        $message = sprintf('<info> %s</info>', $message);
        if ($newLine) {
            $this->writeln($message);
        } else {
            $this->write($message);
        }
    }

    /**
     * @param array|string $message
     * @param bool         $newLine
     */
    public function comment($message, $newLine = true)
    {
        $message = sprintf('<comment> %s</comment>', $message);
        if ($newLine) {
            $this->writeln($message);
        } else {
            $this->write($message);
        }
    }

    /**
     * @param $message
     */
    public function commentBlock($message)
    {
        $this->block(
            $message, null,
            'bg=yellow;fg=black',
            ' ',
            true
        );
    }

    /**
     * @param array  $headers
     * @param array  $rows
     * @param string $style
     */
    public function table(array $headers, array $rows, $style = 'symfony-style-guide')
    {
        $headers = array_map(
            function ($value) {
                return sprintf('<info>%s</info>', $value);
            }, $headers
        );

        if (!is_array(current($rows))) {
            $rows = array_map(
                function ($row) {
                    return [$row];
                },
                $rows
            );
        }

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);

        $table->render();
        $this->newLine();
    }

    /**
     * @param $message
     * @param bool    $newLine
     */
    public function simple($message, $newLine = true)
    {
        $message = sprintf(' %s', $message);
        if ($newLine) {
            $this->writeln($message);
        } else {
            $this->write($message);
        }
    }

    /**
     * @param array|string $message
     */
    public function text($message)
    {
        $message = sprintf('// %s', $message);
        parent::text($message);
    }
}
