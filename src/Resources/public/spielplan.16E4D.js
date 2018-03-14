/**
 * Spielplan für (z.B.) Oberliga
 */

 const spielplan = [
 // Einzel
 { home: [0], away: [0] },
 { home: [1], away: [1] },
 { home: [2], away: [2] },
 { home: [3], away: [3] },
 // - - - - - - - - - - -
 { home: [0], away: [1] },
 { home: [1], away: [0] },
 { home: [2], away: [3] },
 { home: [3], away: [2] },
 // - - - - - - - - - - -
 { home: [2], away: [0] },
 { home: [3], away: [1] },
 { home: [1], away: [2] },
 { home: [0], away: [3] },
 // - - - - - - - - - - -
 { home: [3], away: [0] },
 { home: [2], away: [1] },
 { home: [0], away: [2] },
 { home: [1], away: [3] },
 // - - - - - - - - - - -
 // Doppel
 { home: [0,1], away: [1,0] },
 { home: [2,3], away: [3,2] },
 { home: [2,3], away: [0,1] },
 { home: [0,1], away: [2,3] }
 ];
