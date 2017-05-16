<?php

namespace Urbanara\CatalogPromotionPlugin\Rule;

use Psr\Log\LoggerInterface;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Urbanara\CatalogPromotionPlugin\Exception\CatalogPromotionRuleException;
use Urbanara\CatalogPromotionPlugin\Form\Type\Rule\IsItemDeliveryTimeType;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class IsItemDeliveryTimeRuleChecker implements RuleCheckerInterface
{
    const TYPE = 'is_product_delivery_time_in_scope';

    const PRODUCT_ATTRIBUTE_DELIVERY_TIME = 'eta_date';

    const CRITERIA_MORE = 'more';
    const CRITERIA_LESS = 'less';
    const CRITERIA_EQUAL = 'equal';

    const ERROR_MSG_ETA_NOT_FOUND = "ETA date invalid or not found for product %s";
    const ERROR_MSG_NO_CRITERIA_OR_WEEKS_NUMBER_ITEMS_FOUND =
        "Wrong rule configuration. No criteria or weeks number items found";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFormType() : string
    {
        return IsItemDeliveryTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isEligible(ProductVariantInterface $productVariant, array $configuration) : bool
    {
        $this->logger->critical("RUN AWAY RUN AWAY RUN AWAY");
        $etaAttribute = $productVariant->getProduct()->getAttributeByCodeAndLocale(
            self::PRODUCT_ATTRIBUTE_DELIVERY_TIME
        );

        if (!($etaAttribute instanceof AttributeValueInterface)) {
            throw new CatalogPromotionRuleException(
                sprintf(
                    self::ERROR_MSG_ETA_NOT_FOUND,
                    $productVariant->getProduct()->getId()
                )
            );
        }

        $etaParsedDate = \DateTime::createFromFormat(DATE_ISO8601, $etaAttribute->getValue());

        if (!$etaParsedDate || !($etaParsedDate instanceof \DateTime)) {
            throw new CatalogPromotionRuleException(self::ERROR_MSG_ETA_NOT_FOUND);
        }

        if (!isset($configuration['criteria']) || !isset($configuration['weeks'])) {
            throw new CatalogPromotionRuleException(self::ERROR_MSG_NO_CRITERIA_OR_WEEKS_NUMBER_ITEMS_FOUND);
        }

        return $this->validateElegibility($configuration['criteria'], $configuration['weeks'], $etaParsedDate);
    }

    /**
     * @param string    $criteria
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function validateElegibility(string $criteria, int $referenceDeliveryWeeks, \DateTime $etaDate) : bool
    {
        if ($criteria == self::CRITERIA_MORE) {
            return $this->isGreaterThan($etaDate, $referenceDeliveryWeeks);
        }
        if ($criteria == self::CRITERIA_EQUAL) {
            return $this->isEqualsTo($etaDate, $referenceDeliveryWeeks);
        }
        if ($criteria == self::CRITERIA_LESS) {
            return $this->isLessThan($etaDate, $referenceDeliveryWeeks);
        }

        return false;
    }

    /**
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function isGreaterThan(\DateTime $etaDate, int $referenceDeliveryWeeks) : bool
    {
        return $etaDate > $this->getDateTimeNumWeeksFromNow($referenceDeliveryWeeks);
    }

    /**
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function isEqualsTo(\DateTime $etaDate, int $referenceDeliveryWeeks) : bool
    {
        return $etaDate === $this->getDateTimeNumWeeksFromNow($referenceDeliveryWeeks);
    }

    /**
     * @param \DateTime $etaDate
     * @param int       $referenceDeliveryWeeks
     *
     * @return bool
     */
    private function isLessThan(\DateTime $etaDate, int $referenceDeliveryWeeks) : bool
    {
        return $etaDate < $this->getDateTimeNumWeeksFromNow($referenceDeliveryWeeks);
    }

    /**
     * @param int $numWeeks
     *
     * @return \DateTime
     */
    private function getDateTimeNumWeeksFromNow(int $numWeeks) : \DateTime
    {
        $now = (new \DateTime())->setTime(0, 0, 0, 0);

        if ($numWeeks <= 0) {
            return $now;
        }

        return date_add($now, new \DateInterval("P{$numWeeks}W"));
    }
}