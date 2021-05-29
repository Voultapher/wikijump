import { dequal } from "dequal"
import { klona } from "klona"
import type {
  EmbedToken,
  SerializedTokenizerStack,
  TokenizerStackElement
} from "../types"

/** State/stack handler for the tokenizer. */
export class TokenizerStack {
  /** Embedded language data, if present. */
  declare embedded: null | [lang: string, start: number]

  /**
   * Flag that gets toggled to true if the stack is changed. Does not set
   * itself back - it must be set back manually.
   */
  declare changed: boolean

  /** The internal stack. */
  private declare stack: TokenizerStackElement[]

  /** @param serialized - An existing serialized tokenizer stack. */
  constructor(serialized: SerializedTokenizerStack = { stack: [], embedded: null }) {
    const { stack, embedded } = klona(serialized)
    this.stack = stack
    this.embedded = embedded
    this.changed = false
  }

  /** The last element in the stack. */
  private get last() {
    return this.stack[this.stack.length - 1]
  }

  /** The last element in the stack. */
  private set last(element) {
    this.stack[this.stack.length - 1] = element
  }

  /** The top-most state of the stack. */
  get state() {
    return this.last[0]
  }

  /** The length (depth), or number of stack nodes, in the stack. */
  get depth() {
    return this.stack.length
  }

  /** The parent of the stack, i.e. the state of the stack if it were to be popped. */
  get parent() {
    const parent = this.clone()
    parent.pop()
    return parent
  }

  /** The current context state of the stack. */
  get context() {
    return this.last?.[1] ?? {}
  }

  /** The current context state of the stack. */
  set context(context) {
    this.changed = true
    this.last[1] = context ?? {}
  }

  /**
   * Pushes a new state to the top of the stack.
   *
   * @param state - The state to add.
   * @param context - The context to set. Reuses the last context if not provided.
   */
  push(state: string, context = this.context) {
    this.changed = true
    this.stack.push([state, context])
  }

  /**
   * Switches to a new state, replacing the current one.
   *
   * @param state - The state to add.
   * @param context - The context to set. Reuses the last context if not provided.
   */
  switchTo(state: string, context = this.context) {
    this.changed = true
    this.last = [state, context]
  }

  /** Remove the top-most state of the stack. */
  pop() {
    this.changed = true
    return this.stack.pop()?.[0]
  }

  /** Remove all states from the stack except the very first. */
  popall() {
    this.changed = true
    this.stack = [this.stack.shift() ?? ["root", {}]]
  }

  /** Returns a deep clone of the stack. */
  clone() {
    return new TokenizerStack(this.serialize())
  }

  /**
   * Starts the embedding of a language.
   *
   * @param lang - The language to be embedded.
   * @param start - The start position of the embedded language.
   */
  setEmbedded(lang: string, start: number) {
    this.changed = true
    this.embedded = [lang, start]
  }

  /**
   * Stops the embedding of the currently embedded data, and returns the
   * finalized range. Returns null if no language was being embedded.
   *
   * @param end - The end position of the embedded language.
   */
  endEmbedded(end: number): EmbedToken | null {
    if (!this.embedded) return null
    this.changed = true
    const embedded = this.embedded
    this.embedded = null
    return [...embedded, end]
  }

  /** Serializes the stack and embedded data. */
  serialize(): SerializedTokenizerStack {
    const { stack, embedded } = this
    return { stack: klona(stack), embedded: klona(embedded) }
  }

  /**
   * Compares two stacks and returns if they are equal. They can be pure
   * `TokenizerStack` objects or already serialized.
   */
  static isEqual(
    stack1: SerializedTokenizerStack | TokenizerStack,
    stack2: SerializedTokenizerStack | TokenizerStack
  ) {
    // convert to just string arrays
    if ("serialize" in stack1) stack1 = stack1.serialize()
    if ("serialize" in stack2) stack2 = stack2.serialize()

    return dequal(stack1, stack2)
  }
}
