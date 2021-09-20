export enum Nesting {
  /** Special value which indicates that a nested region should be ended. */
  POP
}

export enum Inclusivity {
  /** The token should be excluded from the parent node. */
  EXCLUSIVE,
  /** The token should be included in the parent node. */
  INCLUSIVE
}

export enum Wrapping {
  /** The {@link Node} in this match contains the entirety of the branch. */
  FULL,
  /** The {@link Node} in this match begins the branch. */
  BEGIN,
  /** The {@link Node} in this match ends the branch. */
  END
}
