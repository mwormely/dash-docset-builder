<?php

namespace App\Docsets;

use Illuminate\Support\Str;
use Wa72\HtmlPageDom\HtmlPage;
use Illuminate\Support\Collection;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use Illuminate\Support\Facades\Storage;

class TailwindCSS extends BaseDocset
{
    public const CODE = 'tailwindcss';
    public const NAME = 'Tailwind CSS';
    public const URL = 'tailwindcss.com';
    public const INDEX = 'installation.html';
    public const PLAYGROUND = 'https://codesandbox.io/s/github/lbogdan/tailwindcss-playground';
    public const ICON_16 = 'favicon-16x16.png';
    public const ICON_32 = 'favicon-32x32.png';
    public const EXTERNAL_DOMAINS = [
        'refactoring-ui.nyc3.cdn.digitaloceanspaces.com',
        'jsdelivr.net'
    ];

    public function entries(string $file): Collection
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $entries = collect();

        $entries = $entries->merge($this->environmentEntries($crawler, $file));
        $entries = $entries->merge($this->instructionEntries($crawler, $file));
        $entries = $entries->merge($this->sampleEntries($crawler, $file));
        $entries = $entries->merge($this->resourceEntries($crawler, $file));
        $entries = $entries->merge($this->guideEntries($crawler, $file));
        $entries = $entries->merge($this->sectionEntries($crawler, $file));

        return $entries;
    }

    protected function environmentEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (basename($file) === 'community.html') {
            $crawler->filter('h2')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
                $entries->push([
                    'name' => $this->cleanAnchorText($node->text()),
                    'type' => 'Environment',
                    'path' => basename($file) . '#' . Str::slug($node->text()),
                ]);
            });

            return $entries;
        }
    }

    protected function instructionEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (basename($file) === 'screencasts.html') {
            $crawler->filter('span.relative')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                    'name' => $this->cleanAnchorText($node->text()),
                    'type' => 'Instruction',
                    'path' => $node->parents()->first()->attr('href'),
                ]);
            });

            return $entries;
        }
    }

    protected function sampleEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (basename($file) === 'components.html') {
            $crawler->filter('span.relative')->each(function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                    'name' => $this->cleanAnchorText($node->text()),
                    'type' => 'Sample',
                    'path' => $node->parents()->first()->attr('href'),
                ]);
            });

            return $entries;
        }
    }

    protected function resourceEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (basename($file) === 'resources.html') {
            $crawler->filter('h2')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
                $entries->push([
                    'name' => $this->cleanAnchorText($node->text()),
                    'type' => 'Resource',
                    'path' => basename($file) . '#' . Str::slug($node->text()),
                ]);
            });

            $crawler->filter('h3')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
                $entries->push([
                    'name' => $this->cleanAnchorText($node->text()),
                    'type' => 'Section',
                    'path' => basename($file) . '#' . Str::slug($node->text()),
                ]);
            });

            return $entries;
        }
    }

    protected function guideEntries(HtmlPageCrawler $crawler, string $file)
    {
        $pageTitle = (new HtmlPage(Storage::get($file)))->getTitle();

        $entries = collect();

        if ($pageTitle === 'Installation - Tailwind CSS') {
            $crawler->filter('#navWrapper li a')->each(static function (HtmlPageCrawler $node) use ($entries) {
                $entries->push([
                    'name' => trim($node->text()),
                    'type' => 'Guide',
                    'path' => $node->attr('href'),
                ]);
            });
        }

        return $entries;
    }

    protected function sectionEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        $crawler->filter('h2')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
            $entries->push([
                'name' => $this->cleanAnchorText($node->text()),
                'type' => 'Section',
                'path' => basename($file) . '#' . Str::slug($node->text()),
            ]);
        });

        return $entries;
    }

    public function format(string $html): string
    {
        $crawler = HtmlPageCrawler::create($html);

        $this->removeNavbarAndHeader($crawler);
        $this->removeLeftSidebar($crawler);
        $this->removeRightSidebar($crawler);
        $this->removeTailwindUIAlert($crawler);
        $this->updateCSS($crawler);
        $this->insertDashTableOfContents($crawler);

        return $crawler->saveHTML();
    }

    protected function removeNavbarAndHeader(HtmlPageCrawler $crawler)
    {
        $crawler->filter('body > div:first-child')->remove();
    }

    protected function removeLeftSidebar(HtmlPageCrawler $crawler)
    {
        $crawler->filter('#sidebar')->remove();
    }

    protected function removeRightSidebar(HtmlPageCrawler $crawler)
    {
        $crawler->filter('#app div.flex > div.hidden')->remove();
    }

    protected function removeTailwindUIAlert(HtmlPageCrawler $crawler)
    {
        $crawler->filter('body > div.transition.transform.fixed.z-100')->remove();
    }

    protected function updateCSS(HtmlPageCrawler $crawler)
    {
        $this->updateTopPadding($crawler);
        $this->updateHeader($crawler);
        $this->updateContainerWidth($crawler);
        $this->updateBottomPadding($crawler);
    }

    protected function updateTopPadding(HtmlPageCrawler $crawler)
    {
        $crawler->filter('#app > div')
            ->removeClass('pt-12')
            ->removeClass('pt-24')
            ->removeClass('pb-16')
            ->removeClass('lg:pt-28')
            ->removeClass('lg:pt-12')
        ;
    }

    protected function updateHeader(HtmlPageCrawler $crawler)
    {
        $crawler->filter('#app > div > div.markdown')
            ->removeClass('lg:ml-0')
            ->removeClass('lg:mr-auto')
            ->removeClass('xl:mx-0')
            ->removeClass('xl:w-3/4')
            ->removeClass('max-w-3xl')
            ->removeClass('xl:px-12')
        ;
    }

    protected function updateContainerWidth(HtmlPageCrawler $crawler)
    {
        $crawler->filter('body > div:first-child')
            ->removeClass('max-w-screen-xl');

        $crawler->filter('#content-wrapper')
            ->removeClass('lg:static')
            ->removeClass('lg:max-h-full')
            ->removeClass('lg:overflow-visible')
            ->removeClass('lg:w-3/4')
            ->removeClass('xl:w-4/5');

        $crawler->filter('#app > div > div.flex > div.markdown')
            ->removeClass('xl:p-12')
            ->removeClass('max-w-3xl')
            ->removeClass('lg:ml-0')
            ->removeClass('lg:mr-auto')
            ->removeClass('xl:w-3/4')
            ->removeClass('xl:px-12')
            ->removeClass('xl:mx-0');
    }

    protected function updateBottomPadding(HtmlPageCrawler $crawler)
    {
        $crawler->filter('body')
            ->addClass('pb-8');
    }

    protected function insertDashTableOfContents(HtmlPageCrawler $crawler)
    {
        $crawler->filter('h1')
            ->before('<a name="//apple_ref/cpp/Section/Top" class="dashAnchor"></a>');

        $crawler->filter('h2, h3')->each(function (HtmlPageCrawler $node) {
            $node->prepend(
                '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Section/' . rawurlencode($this->cleanAnchorText($node->text())) . '" class="dashAnchor"></a>'
            );
        });
    }

    protected function cleanAnchorText($anchorText)
    {
        return trim(preg_replace('/\s+/', ' ', $anchorText));
    }
}
