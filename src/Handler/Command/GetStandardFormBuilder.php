<?php namespace Anomaly\StandardFormExtension\Handler\Command;

use Anomaly\FormsModule\Form\Contract\FormInterface;
use Anomaly\FormsModule\Form\FormAutoresponder;
use Anomaly\FormsModule\Form\FormMailer;
use Anomaly\StandardFormExtension\Handler\StandardFormBuilder;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Routing\Redirector;

/**
 * Class GetStandardFormBuilder
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\StandardFormExtension\Handler\Command
 */
class GetStandardFormBuilder implements SelfHandling
{

    /**
     * The form instance.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * Create a new GetStandardFormBuilder instance.
     *
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * Handle the command.
     *
     * @param StandardFormBuilder $builder
     * @return StandardFormBuilder
     */
    public function handle(StandardFormBuilder $builder, Redirector $redirect)
    {
        $actions = $this->form->getActions();
        $buttons = $this->form->getButtons();
        $stream  = $this->form->getFormEntriesStream();

        $builder->on(
            'saved',
            function (FormMailer $mailer, FormAutoresponder $autoresponder) use ($builder) {
                $mailer->send($this->form, $builder);
                $autoresponder->send($this->form, $builder);
            }
        );

        return $builder
            ->setActions(['submit'])
            ->setActions($actions->toArray())
            ->setButtons($buttons->toArray())
            ->setModel($stream->getEntryModel())
            ->setOption('url', 'forms/handle/' . $this->form->getFormSlug())
            ->setOption('success_message', $this->form->getSuccessMessage() ?: false)
            ->setOption('redirect', $this->form->getSuccessRedirect() ?: $redirect->back());
    }
}