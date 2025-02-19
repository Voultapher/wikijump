<!--
  @component Sheaf Editor: Editor Pane Topbar.
-->
<script lang="ts">
  import { getContext } from "svelte"
  import type { SheafContext } from "../context"
  import { Button } from "@wikijump/components"
  import { throttle } from "@wikijump/util"
  import { t, number, unit } from "@wikijump/api"
  import { Content } from "@wikijump/cm-lang-ftml"

  const { editor, bindings, settings, small } = getContext<SheafContext>("sheaf")

  // -- STATS

  let chars = 0
  let bytes = 0
  let words = 0
  let lines = 0

  // we really don't want to call this function often
  // it has to get the editor's value, which means it has to stringify
  // the document contents, which is expensive and memory intensive
  const updateExpensiveStats = throttle(async () => {
    const value = await $editor.value()
    const stats = await Content.stats(value)
    words = stats.words
    bytes = Math.round(stats.bytes / 1000)
  }, 250)

  $: if ($editor.doc) {
    // we'll update chars and lines here because that's cheap, and it fools the user
    // into thinking that the stats display is super responsive :)
    chars = $editor.doc.length
    lines = $editor.doc.lines
    updateExpensiveStats()
  }

  $: theme = $settings.editor.darkmode ? "dark" : "light"
</script>

<div class="sheaf-pane-editor-topbar {theme} codetheme-{theme}">
  <div class="sheaf-title" aria-hidden="true">
    <span class="sheaf-title-text">{$t("sheaf.TITLE")}</span>
    <span class="sheaf-title-version">{$t("sheaf.VERSION")}</span>
  </div>

  <!-- TODO: figure out how you're actually supposed to make a sideways table -->
  <div class="sheaf-stats">
    <table class="sheaf-stats-column">
      <tr>
        <td>{$t("sheaf.stats.CHARS")}</td>
        <td>{$number(chars, { useGrouping: false })}</td>
      </tr>
      <tr>
        <td>{$t("sheaf.stats.BYTES")}</td>
        <td>
          {$unit(bytes, "kilobyte", { useGrouping: false, unitDisplay: "narrow" })}
        </td>
      </tr>
    </table>
    <table class="sheaf-stats-column">
      <tr>
        <td>{$t("sheaf.stats.WORDS")}</td>
        <td>{$number(words, { useGrouping: false })}</td>
      </tr>
      <tr>
        <td>{$t("sheaf.stats.LINES")}</td>
        <td>{$number(lines, { useGrouping: false })}</td>
      </tr>
    </table>
  </div>

  <!-- Preview open, close button -->
  {#if !$small}
    <div class="sheaf-button-toggle-preview">
      {#if $settings.preview.enabled}
        <Button
          i="fluent:caret-right-24-filled"
          tip={$t("sheaf.tooltips.CLOSE_PREVIEW")}
          size="1.75rem"
          baseline
          on:click={() => ($settings.preview.enabled = false)}
        />
      {:else}
        <Button
          i="fluent:caret-left-24-filled"
          tip={$t("sheaf.tooltips.OPEN_PREVIEW")}
          size="1.75rem"
          baseline
          on:click={() => ($settings.preview.enabled = true)}
        />
      {/if}
    </div>
  {/if}
</div>

<style lang="scss">
  @import "../../../../resources/css/abstracts";

  .sheaf-pane-editor-topbar {
    position: relative;
    z-index: $z-above;
    display: flex;
    grid-area: "topbar";
    gap: 0.75rem;
    align-items: center;
    background: var(--colcode-background);
    box-shadow: -0.5rem 0 0.25rem rgba(black, 0.25);
  }

  .sheaf-stats {
    display: flex;
    gap: 0.5rem;
    font-family: var(--font-mono);
    font-size: 0.75rem;
  }

  .sheaf-stats-column {
    > tr {
      height: 0.825rem;
      line-height: 0;
    }
    > tr > td:nth-child(1) {
      padding-right: 0.25rem;
      color: var(--col-text-dim);
    }
    > tr > td:nth-child(2) {
      color: var(--col-text-subtle);
    }
  }

  .sheaf-title {
    display: inline-flex;
    flex-direction: row;
    gap: 0.25rem;
    align-items: center;
    padding-right: 0.75rem;
    padding-left: 0.5rem;
    border-right: solid 0.125rem var(--colcode-border);
  }

  .sheaf-title-text {
    font-family: var(--font-display);
    font-size: 1rem;
    font-style: italic;
    font-weight: 700;
    color: var(--col-text);
  }

  .sheaf-title-version {
    font-family: var(--font-display);
    font-size: 1rem;
    font-style: italic;
    font-weight: 400;
    color: var(--col-text-subtle);
  }

  .sheaf-button-toggle-preview {
    position: absolute;
    top: 50%;
    right: 0.5rem;
    transform: translateY(-50%);
  }
</style>
