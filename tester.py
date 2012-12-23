def start():
    import sys
    if len(sys.argv) != 3:
        print "Usage: ", sys.argv[0], "<file_path for file to test> <file_path for correct query output>"
        sys.exit()
    with open(sys.argv[2]) as f:
        content = set(f.readlines())
    with open(sys.argv[1]) as f:
        usercontent = f.readlines()
    if len(usercontent) > 0 and usercontent[0].startswith("Error"):
        print "".join(usercontent[:-3]),
    else:
        mismatch =  [str(i-1)+". "+usercontent[i] for i in xrange(len(usercontent)-4) if usercontent[i] not in content]
        if len(mismatch) != 0:
            print "".join(mismatch),

start()
