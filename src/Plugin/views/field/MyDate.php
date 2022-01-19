<?php

namespace Drupal\my_date_views_field\Plugin\views\field;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Field handler to flag the node type.
 *
 * @ViewsField("my_date")
 */
class MyDate extends FieldPluginBase
{

    /**
     * The date formatter service.
     *
     * @var \Drupal\Core\Datetime\DateFormatterInterface
     */
    protected $dateFormatter;

    /**
     * The date format storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface
     */
    protected $dateFormatStorage;

    /**
     * Constructs a new MyDate object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin ID for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
     *   The date formatter service.
     * @param \Drupal\Core\Entity\EntityStorageInterface $date_format_storage
     *   The date format storage.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter, EntityStorageInterface $date_format_storage) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->dateFormatter = $date_formatter;
        $this->dateFormatStorage = $date_format_storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('date.formatter'),
            $container->get('entity_type.manager')->getStorage('date_format')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        $options = parent::defineOptions();

        $options['format_date_time']   = ['default' => 'full'];
        $options['custom_date_format'] = ['default' => ''];
        $options['timezone']           = ['default' => ''];
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(&$form, FormStateInterface $form_state)
    {
        $dateFormats = [];
        foreach ($this->dateFormatStorage->loadMultiple() as $machineName => $value) {
            $dateFormats[$machineName] = $this->t('@name format: @date', ['@name' => $value->label(), '@date' => $this->dateFormatter->format(REQUEST_TIME, $machineName)]);
        }

        $form['format_date_time'] = [
            '#type'          => 'select',
            '#title'         => $this->t('Date Time Format'),
            '#options'       => [
                'raw'           => $this->t('Raw from Database'),
                'raw date only' => $this->t('Raw Date Only'),
                'custom'        => $this->t('Custom'),
            ] + $dateFormats,
            '#default_value' => isset($this->options['format_date_time']) ? $this->options['format_date_time'] : 'raw',
        ];

        $form['custom_date_format'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('Custom date format'),
            '#description'   => $this->t('See <a href="http://us.php.net/manual/en/function.date.php" target="_blank">the PHP docs</a> for date formats.'),
            '#default_value' => isset($this->options['custom_date_format']) ? $this->options['custom_date_format'] : '',
            '#states'        => [
                'visible'        => [
                    ':input[name="options[format_date_time]"]' => ['value' => 'custom'],
                ],
            ],
        ];

        $form['timezone'] = [
            '#type'          => 'select',
            '#title'         => $this->t('Timezone'),
            '#description'   => $this->t('Timezone to be used for date output.'),
            '#options'       => ['' => $this->t('- Default site/user timezone -')] + system_time_zones(FALSE, TRUE),
            '#default_value' => $this->options['timezone'],
            '#states'        => [
                'visible'          => [
                    ':input[name="options[format_date_time]"]' => ['value' => 'custom'],
                ],
            ],
        ];

        parent::buildOptionsForm($form, $form_state);
    }

    /**
     * Render function for the my_date field.
     *
     * Display a date
     *
     * @{inheritdoc}
     */
    public function render(ResultRow $values)
    {

        $value            = $this->getValue($values);
        $formatDateTime   = $this->options['format_date_time'];
        $drupalDateTime   = new DrupalDateTime($value, new \DateTimeZone('UTC'));
        $timestamp        = $drupalDateTime->getTimestamp();
        $customDateFormat = $this->options['custom_date_format'];
        $timezone         = !empty($this->options['timezone']) ? $this->options['timezone'] : NULL;

        if ($value) {
            switch($formatDateTime) {
                case 'raw':
                    return $this->sanitizeValue($value);
                case 'raw date only':
                    return $this->sanitizeValue(substr($value, 0, 10));
                case 'custom':
                    if (trim($customDateFormat) == '') {
                        return $this->sanitizeValue($value);
                    }
                    if ($customDateFormat == 'r') {
                        return $this->dateFormatter->format($timestamp, $formatDateTime, $customDateFormat, $timezone, 'en');
                    }
                    return $this->dateFormatter->format($timestamp, $formatDateTime, $customDateFormat, $timezone);
                default:
                    return $this->dateFormatter->format($timestamp, $formatDateTime);
            }
        }

    }

}
