<!--
  @component Tab panel designed to be used with the `Tabview` component.

  Usage:
  ```svelte
    <Tabview>
      <Tab>
        <span slot="button">Tab selector button text.</span>
        Tab panel contents.
      </Tab>
    </Tabview>
  ```
-->
<script lang="ts">
  import { getContext } from "svelte"
  import { createID } from "@wikijump/util"
  import { portal } from "../lib/portal"
  import Button from "../Button.svelte"
  import type { Writable } from "svelte/store"

  const id = createID()

  const buttonID = `tab-button${id}`
  const panelID = `tab-panel${id}`

  interface Tabs {
    buttons?: HTMLElement
    key: Writable<any>
    conditional: boolean
  }

  const { buttons, key, conditional } = getContext<Required<Tabs>>("tabs")

  let selected = false
  $: selected = $key === id

  // if the store has no tab selected, set the start tab to this tab
  if (!$key) selectThis()

  function selectThis() {
    $key = id
  }
</script>

<span
  class="tab-button"
  class:is-selected={selected}
  use:portal={buttons}
  role="presentation"
>
  <Button
    baseline
    sharp
    wide
    active={selected}
    on:click={selectThis}
    id={buttonID}
    aria-controls={panelID}
    aria-selected={String(selected)}
  >
    <slot name="button" />
  </Button>
</span>

<div
  class="tab-panel"
  hidden={!selected}
  id={panelID}
  aria-labelledby={buttonID}
  tabindex="0"
>
  {#if selected || !conditional}<slot />{/if}
</div>

<style lang="scss">
  @import "../../../../resources/css/abstracts";

  .tab-button {
    flex-grow: 1;
    font-family: var(--font-display);
    font-size: 1.125rem;
    font-weight: 500;
    text-align: center;
    border-bottom: solid 1px var(--col-border);
    border-left: solid 1px var(--col-border);

    &:first-child {
      border-left: none;
    }
  }

  .tab-panel {
    outline: none;
  }

  @include tolerates-motion {
    .tab-panel {
      animation: tab-panel-reveal 0.125s 0s 1 backwards ease-out;
    }
  }

  @keyframes tab-panel-reveal {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }
</style>
