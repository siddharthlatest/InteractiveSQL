def start():
    import sys
    LINE_PADDER = 0
    if len(sys.argv) not in [3,4] or (len(sys.argv) is 4 and '--RA' not in sys.argv) \
          or (len(sys.argv) is 3 and '--RA' in sys.argv):
        print "Usage: ", sys.argv[0], "[--RA] <file_path for file to test> <file_path for solution query>."
        print "--RA indicates RA mode. The default check is in SQL mode."
        sys.exit()
    if len(sys.argv) is 4:
        LINE_PADDER=4
        sys.argv.remove('--RA')
    with open(sys.argv[2]) as f:
        contentL = f.readlines()
        content = set(contentL)
    with open(sys.argv[1]) as f:
        usercontent = f.readlines()
    if len(usercontent) > 0 and usercontent[0].startswith("Error"):
        print "- "+"- ".join(usercontent[:-(LINE_PADDER-1)]),
    else:
        mismatch =  [usercontent[i] for i in \
              xrange(len(usercontent)-LINE_PADDER) if usercontent[i] not in content]
        if len(mismatch) != 0:
            print "- "+"- ".join(mismatch),
        missing = [con for con in contentL[:len(content)-LINE_PADDER] if con not in usercontent]
        if len(missing) != 0:
            print "* "+"* ".join(missing),

start()
