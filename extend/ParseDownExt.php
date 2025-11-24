<?php

namespace extend;

use Highlight\Highlighter;
use Parsedown;
use Throwable;

class ParseDownExt extends Parsedown
{
    protected Highlighter $highlighter;

    public function __construct()
    {
        $this->highlighter = new Highlighter;
    }

    /**
     * 每个标签都会调用的方法重写
     * 1. 为每个标签都添加一个 class，微信小程序只支持 class 选择器
     */
    protected function element(array $Element): string
    {
        $Element['attributes']['class'] = "tag-{$Element['name']}" . (isset($Element['attributes']['class']) ? " {$Element['attributes']['class']}" : '');
        return parent::element($Element);
    }

    /**
     * 链接处理重写
     * 1. 为每个 a 标签增加 target="_blank"
     */
    protected function inlineLink($Excerpt): ?array
    {
        $link = parent::inlineLink($Excerpt);
        if (!isset($link)) {
            return null;
        }
        $link['element']['attributes']['target'] = '_blank';
        return $link;
    }

    /**
     * 代码块处理
     * 1. 实现代码高亮，此处分析语言语法，组织结构和添加 class
     * 2. 配对的 css 代码获取：$content .= "<style>" . HighlightUtilities\getStyleSheet('vs2015') . "</style>";
     * 3. 一篇 markdown 只需添加一次配对的 css 代码，所以不再此处添加，请自行拼接到文章内容头部，甚至可以不在服务端添加 css 代码
     */
    protected function blockFencedCodeComplete($Block): array
    {
        if (!isset($Block['element']['text'])) {
            return $Block;
        }

        $code          = $Block['element']['text']['text'];
        $languageClass = $Block['element']['text']['attributes']['class'];
        $language      = explode('-', $languageClass);

        try {
            $highlighted                                     = $this->highlighter->highlight($language[1], $code);
            $Block['element']['text']['attributes']['class'] = vsprintf('%s hljs %s', [
                $languageClass,
                $highlighted->language,
            ]);
            $Block['element']['text']['rawHtml']             = $highlighted->value;
            unset($Block['element']['text']['text']);
        } catch (Throwable) {
            //
        }

        return $Block;
    }
}